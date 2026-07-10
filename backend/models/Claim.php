<?php

class Claim
{
    private PDO $conn;
    private string $table = "claims";

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    public function create(array $data): bool
    {
        $userId = (int)($data['user_id'] ?? 0);
        $message = trim((string)($data['claim_message'] ?? ''));
        $matchId = isset($data['match_id']) && $data['match_id'] !== null
            ? (int)$data['match_id']
            : null;
        $itemId = isset($data['item_id']) && $data['item_id'] !== null
            ? (int)$data['item_id']
            : null;
        $itemType = $data['item_type'] ?? null;
        $lostItemId = isset($data['lost_item_id']) && $data['lost_item_id'] !== ''
            ? (int)$data['lost_item_id']
            : null;

        if($userId <= 0 || $message === '') {
            return false;
        }

        if($matchId === null && ($itemId === null || $itemType === null)) {
            return false;
        }

        if($matchId !== null && $itemId !== null) {
            return false;
        }

        if($matchId !== null && $lostItemId !== null) {
            return false;
        }

        if($lostItemId !== null) {
            $stmt = $this->conn->prepare(
                "SELECT id
                 FROM lost_items
                 WHERE id = :lost_item_id
                 AND user_id = :user_id
                 AND status = 'pending'"
            );
            $stmt->execute([
                ':lost_item_id' => $lostItemId,
                ':user_id' => $userId
            ]);

            if(!$stmt->fetchColumn()) {
                return false;
            }
        }

        $sql = "INSERT INTO {$this->table}
                (user_id, match_id, item_id, item_type, lost_item_id, claim_message, status)
                VALUES (:user_id, :match_id, :item_id, :item_type, :lost_item_id, :message, 'pending')";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':user_id' => $userId,
            ':match_id' => $matchId,
            ':item_id' => $itemId,
            ':item_type' => $itemType,
            ':lost_item_id' => $lostItemId,
            ':message' => $message
        ]);
    }

    private function claimsSelectSql(string $where = ''): string
    {
        return "
            SELECT
                c.id,
                c.user_id,
                c.match_id,
                c.item_id,
                c.item_type,
                c.lost_item_id AS direct_lost_item_id,
                c.claim_message,
                c.status,
                c.created_at,

                u.fullname,
                u.email,

                COALESCE(l.id, direct_lost.id) AS lost_item_id,
                COALESCE(l.item_name, direct_lost.item_name) AS lost_item,
                COALESCE(l.category, direct_lost.category) AS lost_category,
                COALESCE(l.color, direct_lost.color) AS lost_color,
                COALESCE(l.brand_model, direct_lost.brand_model) AS lost_brand_model,
                COALESCE(l.unique_features, direct_lost.unique_features) AS lost_unique_features,
                COALESCE(l.description, direct_lost.description) AS lost_description,
                COALESCE(l.location_lost, direct_lost.location_lost) AS location_lost,
                COALESCE(l.date_lost, direct_lost.date_lost) AS date_lost,
                COALESCE(l.image, direct_lost.image) AS lost_image,
                COALESCE(l.status, direct_lost.status) AS lost_status,

                COALESCE(f.id, direct_found.id) AS found_item_id,
                COALESCE(f.item_name, direct_found.item_name) AS found_item,
                COALESCE(f.category, direct_found.category) AS found_category,
                COALESCE(f.color, direct_found.color) AS found_color,
                COALESCE(f.brand_model, direct_found.brand_model) AS found_brand_model,
                COALESCE(f.unique_features, direct_found.unique_features) AS found_unique_features,
                COALESCE(f.description, direct_found.description) AS found_description,
                COALESCE(f.location_found, direct_found.location_found) AS location_found,
                COALESCE(f.date_found, direct_found.date_found) AS date_found,
                COALESCE(f.image, direct_found.image) AS found_image,
                COALESCE(f.status, direct_found.status) AS found_status,
                direct_found.item_name AS direct_item,

                CASE
                    WHEN c.match_id IS NULL THEN 'direct'
                    ELSE 'match'
                END AS claim_type

            FROM claims c

            INNER JOIN users u
                ON c.user_id = u.id

            LEFT JOIN matches m
                ON c.match_id = m.id

            LEFT JOIN lost_items l
                ON m.lost_item_id = l.id

            LEFT JOIN found_items f
                ON m.found_item_id = f.id

            LEFT JOIN found_items direct_found
                ON c.item_type = 'found'
                AND c.item_id = direct_found.id

            LEFT JOIN lost_items direct_lost
                ON c.match_id IS NULL
                AND c.lost_item_id = direct_lost.id

            {$where}
        ";
    }

    private function notifyUser(int $userId, string $message, string $link = '/user/notifications.php'): void
    {
        if($this->notificationSupportsLinks()) {
            $stmt = $this->conn->prepare(
                "INSERT INTO notifications
                 (user_id, message, link, is_read)
                 VALUES
                 (:user_id, :message, :link, 0)"
            );

            $stmt->execute([
                ':user_id' => $userId,
                ':message' => $message,
                ':link' => $link
            ]);

            return;
        }

        $stmt = $this->conn->prepare(
            "INSERT INTO notifications
             (user_id, message, is_read)
             VALUES
             (:user_id, :message, 0)"
        );

        $stmt->execute([
            ':user_id' => $userId,
            ':message' => $message
        ]);
    }

    private function notificationSupportsLinks(): bool
    {
        $stmt = $this->conn->prepare(
            "SHOW COLUMNS
             FROM notifications
             LIKE 'link'"
        );
        $stmt->execute();

        return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getClaimSummary(int $id): ?array
    {
        $sql = $this->claimsSelectSql("WHERE c.id = :id") . "
            LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':id' => $id
        ]);

        $claim = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$claim) {
            return null;
        }

        $itemName = (($claim['claim_type'] ?? 'match') === 'direct')
            ? ($claim['direct_item'] ?? $claim['found_item'] ?? 'the item')
            : ($claim['found_item'] ?? $claim['lost_item'] ?? 'the matched item');

        return [
            'user_id' => (int)$claim['user_id'],
            'item_name' => $itemName
        ];
    }

    public function getAllClaims(): array
{
    $sql = $this->claimsSelectSql() . "
            ORDER BY c.created_at DESC
        ";

    $stmt = $this->conn->prepare($sql);

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    public function getPendingClaims(): array
{
    $sql = $this->claimsSelectSql("WHERE c.status = :status") . "
            ORDER BY c.created_at DESC
        ";

    $stmt = $this->conn->prepare($sql);

    $stmt->execute([
        ':status' => 'pending'
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    public function getClaimById(int $id): ?array
    {
        $sql = $this->claimsSelectSql("WHERE c.id = :id") . "
            LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id'=>$id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function approveClaim(int $id): bool
{
    try {

        $this->conn->beginTransaction();

        /*
        |--------------------------------------------------------------------------
        | Get the Match ID
        |--------------------------------------------------------------------------
        */

        $stmt = $this->conn->prepare(
            "SELECT user_id, match_id, item_id, item_type, lost_item_id, status
             FROM claims
             WHERE id = :id"
        );

        $stmt->execute([
            ':id' => $id
        ]);

        $claim = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$claim) {
            throw new Exception("Claim not found.");
        }

	if ($claim['status'] !== 'pending') {
	    throw new Exception("Claim has already been processed.");
	}

        $matchId = $claim['match_id'];

        if (!$matchId) {
            $stmt = $this->conn->prepare(
                "UPDATE claims
                 SET status = 'approved'
                 WHERE id = :id"
            );

            $stmt->execute([
                ':id' => $id
            ]);

            if ($claim['item_type'] === 'found' && !empty($claim['item_id'])) {
                $stmt = $this->conn->prepare(
                    "UPDATE found_items
                     SET status = 'matched'
                     WHERE id = :item_id"
                );

                $stmt->execute([
                    ':item_id' => $claim['item_id']
                ]);
            }

            if (!empty($claim['lost_item_id'])) {
                $stmt = $this->conn->prepare(
                    "UPDATE lost_items
                     SET status = 'matched'
                     WHERE id = :lost_item_id
                     AND user_id = :user_id"
                );
                $stmt->execute([
                    ':lost_item_id' => $claim['lost_item_id'],
                    ':user_id' => $claim['user_id']
                ]);
            }

            $summary = $this->getClaimSummary($id);
            if($summary) {
                $this->notifyUser(
                    $summary['user_id'],
                    "Your claim for " . $summary['item_name'] . " has been approved. Please wait for collection instructions.",
                    "/user/claims.php"
                );
            }

            $this->conn->commit();

            return true;
        }

        /*
        |--------------------------------------------------------------------------
        | Get Lost Item & Found Item IDs
        |--------------------------------------------------------------------------
        */

        $stmt = $this->conn->prepare(
            "SELECT
                lost_item_id,
                found_item_id
             FROM matches
             WHERE id = :match_id"
        );

        $stmt->execute([
            ':match_id' => $matchId
        ]);

        $match = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$match) {
            throw new Exception("Match not found.");
        }

        /*
        |--------------------------------------------------------------------------
        | Approve Claim
        |--------------------------------------------------------------------------
        */

        $stmt = $this->conn->prepare(
            "UPDATE claims
             SET status = 'approved'
             WHERE id = :id"
        );

        $stmt->execute([
            ':id' => $id
        ]);

        /*
        |--------------------------------------------------------------------------
        | Approve Match
        |--------------------------------------------------------------------------
        */

        $stmt = $this->conn->prepare(
            "UPDATE matches
             SET status = 'approved'
             WHERE id = :match_id"
        );

        $stmt->execute([
            ':match_id' => $matchId
        ]);

        /*
        |--------------------------------------------------------------------------
        | Update Lost Item
        |--------------------------------------------------------------------------
        */

        $stmt = $this->conn->prepare(
            "UPDATE lost_items
             SET status = 'matched'
             WHERE id = :lost_item_id"
        );

        $stmt->execute([
            ':lost_item_id' => $match['lost_item_id']
        ]);

        /*
        |--------------------------------------------------------------------------
        | Update Found Item
        |--------------------------------------------------------------------------
        */

        $stmt = $this->conn->prepare(
            "UPDATE found_items
             SET status = 'matched'
             WHERE id = :found_item_id"
        );

        $stmt->execute([
            ':found_item_id' => $match['found_item_id']
        ]);

        $summary = $this->getClaimSummary($id);
        if($summary) {
            $this->notifyUser(
                $summary['user_id'],
                "Your claim for " . $summary['item_name'] . " has been approved. Please wait for collection instructions.",
                "/user/claims.php"
            );
        }

        $this->conn->commit();

        return true;

    } catch (Exception $e) {

        $this->conn->rollBack();

        return false;

    }
}

    public function collectClaim(int $id): bool
{
    try {

        $this->conn->beginTransaction();

        $stmt = $this->conn->prepare(
            "SELECT user_id, match_id, item_id, item_type, lost_item_id, status
             FROM claims
             WHERE id = :id"
        );

        $stmt->execute([
            ':id' => $id
        ]);

        $claim = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$claim || $claim['status'] !== 'approved') {
            throw new Exception("Only approved claims can be collected.");
        }

        $stmt = $this->conn->prepare(
            "UPDATE claims
             SET status = 'collected'
             WHERE id = :id"
        );

        $stmt->execute([
            ':id' => $id
        ]);

        if (!empty($claim['match_id'])) {
            $stmt = $this->conn->prepare(
                "SELECT lost_item_id, found_item_id
                 FROM matches
                 WHERE id = :match_id"
            );

            $stmt->execute([
                ':match_id' => $claim['match_id']
            ]);

            $match = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$match) {
                throw new Exception("Match not found.");
            }

            $stmt = $this->conn->prepare(
                "UPDATE lost_items
                 SET status = 'claimed'
                 WHERE id = :lost_item_id"
            );

            $stmt->execute([
                ':lost_item_id' => $match['lost_item_id']
            ]);

            $stmt = $this->conn->prepare(
                "UPDATE found_items
                 SET status = 'returned'
                 WHERE id = :found_item_id"
            );

            $stmt->execute([
                ':found_item_id' => $match['found_item_id']
            ]);
        } elseif ($claim['item_type'] === 'found' && !empty($claim['item_id'])) {
            $stmt = $this->conn->prepare(
                "UPDATE found_items
                 SET status = 'returned'
                 WHERE id = :item_id"
            );

            $stmt->execute([
                ':item_id' => $claim['item_id']
            ]);

            if (!empty($claim['lost_item_id'])) {
                $stmt = $this->conn->prepare(
                    "UPDATE lost_items
                     SET status = 'claimed'
                     WHERE id = :lost_item_id
                     AND user_id = :user_id"
                );
                $stmt->execute([
                    ':lost_item_id' => $claim['lost_item_id'],
                    ':user_id' => $claim['user_id']
                ]);
            }
        }

        $summary = $this->getClaimSummary($id);
        if($summary) {
            $this->notifyUser(
                $summary['user_id'],
                "Your claim for " . $summary['item_name'] . " has been marked as collected.",
                "/user/dashboard.php"
            );
        }

        $this->conn->commit();

        return true;

    } catch (Exception $e) {

        $this->conn->rollBack();

        return false;

    }
}

    public function rejectClaim(int $id): bool
	{
	    try {

	        $this->conn->beginTransaction();

	        $stmt = $this->conn->prepare(
	            "SELECT user_id, match_id, item_id, item_type, lost_item_id, status
	             FROM claims
	             WHERE id = :id"
	        );

	        $stmt->execute([
	            ':id' => $id
	        ]);

	        $claim = $stmt->fetch(PDO::FETCH_ASSOC);

	        if (!$claim) {
	            throw new Exception("Claim not found.");
	        }

	        if ($claim['status'] === 'collected') {
	            throw new Exception("Collected claims cannot be rejected.");
	        }

	        if ($claim['status'] === 'rejected') {
	            throw new Exception("Claim has already been rejected.");
	        }

	        if ($claim['status'] === 'approved') {
	            if (!empty($claim['match_id'])) {
	                $stmt = $this->conn->prepare(
	                    "SELECT lost_item_id, found_item_id
	                     FROM matches
	                     WHERE id = :match_id"
	                );

	                $stmt->execute([
	                    ':match_id' => $claim['match_id']
	                ]);

	                $match = $stmt->fetch(PDO::FETCH_ASSOC);

	                if (!$match) {
	                    throw new Exception("Match not found.");
	                }

	                $stmt = $this->conn->prepare(
	                    "UPDATE lost_items
	                     SET status = 'pending'
	                     WHERE id = :lost_item_id"
	                );

	                $stmt->execute([
	                    ':lost_item_id' => $match['lost_item_id']
	                ]);

	                $stmt = $this->conn->prepare(
	                    "UPDATE found_items
	                     SET status = 'pending'
	                     WHERE id = :found_item_id"
	                );

	                $stmt->execute([
	                    ':found_item_id' => $match['found_item_id']
	                ]);
	            } elseif ($claim['item_type'] === 'found' && !empty($claim['item_id'])) {
	                $stmt = $this->conn->prepare(
	                    "UPDATE found_items
	                     SET status = 'pending'
	                     WHERE id = :item_id"
	                );

	                $stmt->execute([
	                    ':item_id' => $claim['item_id']
	                ]);

	                if (!empty($claim['lost_item_id'])) {
	                    $stmt = $this->conn->prepare(
	                        "UPDATE lost_items
	                         SET status = 'pending'
	                         WHERE id = :lost_item_id
	                         AND user_id = :user_id"
	                    );
	                    $stmt->execute([
	                        ':lost_item_id' => $claim['lost_item_id'],
	                        ':user_id' => $claim['user_id']
	                    ]);
	                }
	            }
	        }

	        $stmt = $this->conn->prepare(
	            "UPDATE claims
	             SET status = 'rejected'
	             WHERE id = :id"
	        );

	        $stmt->execute([
	            ':id' => $id
	        ]);

	        if (!empty($claim['match_id'])) {
	            $matchStatus = ($claim['status'] === 'approved') ? 'rejected' : 'approved';

	            $stmt = $this->conn->prepare(
	                "UPDATE matches
	                 SET status = :status
	                 WHERE id = :match_id"
	            );

	            $stmt->execute([
	                ':status' => $matchStatus,
	                ':match_id' => $claim['match_id']
	            ]);
	        }

	        $summary = $this->getClaimSummary($id);

	        if($summary) {
	            $this->notifyUser(
	                $summary['user_id'],
                "Your claim for " . $summary['item_name'] . " has been rejected.",
                "/user/claims.php"
            );
        }

        $this->conn->commit();

        return true;

    } catch(Exception $e){

        $this->conn->rollBack();

        return false;

    }
  } 
  
    public function countPendingClaims(): int
{
    $stmt = $this->conn->prepare(
        "SELECT COUNT(*)
         FROM claims
         WHERE status='pending'"
    );

    $stmt->execute();

    return (int)$stmt->fetchColumn();
}

public function getClaimsByUser(int $userId): array
{
    $sql = $this->claimsSelectSql("WHERE c.user_id = :user_id") . "
            ORDER BY c.created_at DESC
        ";

    $stmt = $this->conn->prepare($sql);

    $stmt->execute([
        ':user_id' => $userId
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}
