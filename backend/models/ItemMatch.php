<?php

class ItemMatch
{
    private PDO $conn;
    private string $table = "matches";

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    public function create(int $lostItemId, int $foundItemId, float $confidenceScore): bool
    {
        try {
            $this->conn->beginTransaction();

            $check = $this->conn->prepare(
                "SELECT id
                 FROM {$this->table}
                 WHERE lost_item_id = :lost_item_id
                 AND found_item_id = :found_item_id
                 AND status != 'rejected'
                 LIMIT 1"
            );

            $check->execute([
                ':lost_item_id' => $lostItemId,
                ':found_item_id' => $foundItemId
            ]);

            if($check->fetch()) {
                throw new Exception("Match already exists.");
            }

            $sql = "INSERT INTO {$this->table}
                    (lost_item_id, found_item_id, confidence_score, status)
                    VALUES (:lost_item_id,:found_item_id,:confidence_score,'pending')";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':lost_item_id' => $lostItemId,
                ':found_item_id' => $foundItemId,
                ':confidence_score' => $confidenceScore
            ]);

            $stmt = $this->conn->prepare(
                "UPDATE lost_items
                 SET status = 'matched'
                 WHERE id = :id"
            );
            $stmt->execute([':id' => $lostItemId]);

            $stmt = $this->conn->prepare(
                "UPDATE found_items
                 SET status = 'matched'
                 WHERE id = :id"
            );
            $stmt->execute([':id' => $foundItemId]);

            $this->notifyMatchOwner($lostItemId, $foundItemId);

            $this->conn->commit();

            return true;
        } catch(Exception $e) {
            $this->conn->rollBack();

            return false;
        }
    }

    private function notifyMatchOwner(int $lostItemId, int $foundItemId): void
    {
        $stmt = $this->conn->prepare(
            "SELECT
                l.user_id,
                l.item_name AS lost_item,
                f.item_name AS found_item
             FROM lost_items l
             INNER JOIN found_items f
                ON f.id = :found_item_id
             WHERE l.id = :lost_item_id
             LIMIT 1"
        );

        $stmt->execute([
            ':lost_item_id' => $lostItemId,
            ':found_item_id' => $foundItemId
        ]);

        $match = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$match) {
            return;
        }

        $params = [
            ':user_id' => $match['user_id'],
            ':message' => "A possible match was found for " . $match['lost_item'] . ": " . $match['found_item'] . "."
        ];

        if($this->notificationSupportsLinks()) {
            $stmt = $this->conn->prepare(
                "INSERT INTO notifications
                 (user_id, message, link, is_read)
                 VALUES
                 (:user_id, :message, :link, 0)"
            );
            $params[':link'] = '/user/matches.php';
        } else {
            $stmt = $this->conn->prepare(
                "INSERT INTO notifications
                 (user_id, message, is_read)
                 VALUES
                 (:user_id, :message, 0)"
            );
        }

        $stmt->execute($params);
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

    public function getPendingLostItems(): array
    {
        $stmt = $this->conn->prepare(
            "SELECT id, item_name, category, location_lost, date_lost
             FROM lost_items
             WHERE status = 'available'
             ORDER BY created_at DESC"
        );
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPendingFoundItems(): array
    {
        $stmt = $this->conn->prepare(
            "SELECT id, item_name, category, location_found, date_found
             FROM found_items
             WHERE status = 'available'
             ORDER BY created_at DESC"
        );
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllMatches(): array
    {
        $sql = "SELECT
                    m.*,
                    l.item_name AS lost_item,
                    l.category,
                    l.location_lost,
                    f.item_name AS found_item,
                    f.location_found
                FROM matches m
                INNER JOIN lost_items l ON m.lost_item_id=l.id
                INNER JOIN found_items f ON m.found_item_id=f.id
                ORDER BY m.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPendingMatches(): array
    {
        $sql = "SELECT
                    m.*,
                    l.item_name AS lost_item,
                    l.category,
                    l.location_lost,
                    f.item_name AS found_item,
                    f.location_found
                FROM matches m
                INNER JOIN lost_items l ON m.lost_item_id=l.id
                INNER JOIN found_items f ON m.found_item_id=f.id
                WHERE m.status=:status
                ORDER BY m.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':status'=>'pending']);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMatchesByUser(int $userId): array
    {
        $sql = "SELECT
                    m.id,
                    m.confidence_score,
                    m.status,
                    m.created_at,
                    l.item_name AS lost_item,
                    l.category,
                    f.item_name AS found_item
                FROM matches m
                INNER JOIN lost_items l ON m.lost_item_id=l.id
                INNER JOIN found_items f ON m.found_item_id=f.id
                WHERE l.user_id=:user_id
                ORDER BY m.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id'=>$userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMatchById(int $id): ?array
    {
        $sql = "SELECT
                    m.*,
                    l.item_name AS lost_item,
                    l.category,
                    l.location_lost,
                    f.item_name AS found_item,
                    f.location_found
                FROM matches m
                INNER JOIN lost_items l ON m.lost_item_id=l.id
                INNER JOIN found_items f ON m.found_item_id=f.id
                WHERE m.id=:id
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id'=>$id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function approveMatch(int $id): bool
    {
        try {
            $this->conn->beginTransaction();

            $match = $this->getMatchById($id);

            if(!$match || $match['status'] !== 'pending') {
                throw new Exception("Only pending matches can be approved.");
            }

            $stmt = $this->conn->prepare(
                "UPDATE matches SET status='approved' WHERE id=:id"
            );
            $stmt->execute([':id'=>$id]);

            $stmt = $this->conn->prepare(
                "UPDATE lost_items
                 SET status = 'matched'
                 WHERE id = :id"
            );
            $stmt->execute([':id' => $match['lost_item_id']]);

            $stmt = $this->conn->prepare(
                "UPDATE found_items
                 SET status = 'matched'
                 WHERE id = :id"
            );
            $stmt->execute([':id' => $match['found_item_id']]);

            $this->conn->commit();

            return true;
        } catch(Exception $e) {
            $this->conn->rollBack();

            return false;
        }
    }

    public function rejectMatch(int $id): bool
    {
        try {
            $this->conn->beginTransaction();

            $match = $this->getMatchById($id);

            if(!$match || $match['status'] === 'collected') {
                throw new Exception("Match cannot be rejected.");
            }

            $stmt = $this->conn->prepare(
                "UPDATE matches SET status='rejected' WHERE id=:id"
            );
            $stmt->execute([':id'=>$id]);

            $stmt = $this->conn->prepare(
                "UPDATE lost_items
                 SET status = 'available'
                 WHERE id = :id"
            );
            $stmt->execute([':id' => $match['lost_item_id']]);

            $stmt = $this->conn->prepare(
                "UPDATE found_items
                 SET status = 'available'
                 WHERE id = :id"
            );
            $stmt->execute([':id' => $match['found_item_id']]);

            $this->conn->commit();

            return true;
        } catch(Exception $e) {
            $this->conn->rollBack();

            return false;
        }
    }

    public function deleteMatch(int $id): bool
    {
        try {
            $this->conn->beginTransaction();

            $match = $this->getMatchById($id);

            if(!$match) {
                throw new Exception("Match not found.");
            }

            $stmt = $this->conn->prepare(
                "DELETE FROM matches WHERE id=:id"
            );
            $stmt->execute([':id'=>$id]);

            if($match['status'] !== 'collected') {
                $stmt = $this->conn->prepare(
                    "UPDATE lost_items
                     SET status = 'available'
                     WHERE id = :id"
                );
                $stmt->execute([':id' => $match['lost_item_id']]);

                $stmt = $this->conn->prepare(
                    "UPDATE found_items
                     SET status = 'available'
                     WHERE id = :id"
                );
                $stmt->execute([':id' => $match['found_item_id']]);
            }

            $this->conn->commit();

            return true;
        } catch(Exception $e) {
            $this->conn->rollBack();

            return false;
        }
    }

    public function countPendingMatches(): int
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM matches WHERE status='available'"
        );
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }
}
