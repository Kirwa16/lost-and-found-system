<?php

class Claim
{
    private PDO $conn;
    private string $table = "claims";

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    public function create(int $userId, int $matchId, string $message): bool
    {
        $sql = "INSERT INTO {$this->table}
                (user_id, match_id, claim_message, status)
                VALUES (:user_id,:match_id,:message,'pending')";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':user_id' => $userId,
            ':match_id' => $matchId,
            ':message' => $message
        ]);
    }

    public function getAllClaims(): array
{
    $sql = "
        SELECT
            c.id,
            c.user_id,
            c.match_id,
            c.claim_message,
            c.status,
            c.created_at,

            u.fullname,
            u.email,

            l.item_name AS lost_item,
            f.item_name AS found_item

        FROM claims c

        INNER JOIN users u
            ON c.user_id = u.id

        INNER JOIN matches m
            ON c.match_id = m.id

        INNER JOIN lost_items l
            ON m.lost_item_id = l.id

        INNER JOIN found_items f
            ON m.found_item_id = f.id

        ORDER BY c.created_at DESC
    ";

    $stmt = $this->conn->prepare($sql);

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    public function getPendingClaims(): array
{
    $sql = "
        SELECT
            c.id,
            c.user_id,
            c.match_id,
            c.claim_message,
            c.status,
            c.created_at,

            u.fullname,
            u.email,

            l.item_name AS lost_item,
            f.item_name AS found_item

        FROM claims c

        INNER JOIN users u
            ON c.user_id = u.id

        INNER JOIN matches m
            ON c.match_id = m.id

        INNER JOIN lost_items l
            ON m.lost_item_id = l.id

        INNER JOIN found_items f
            ON m.found_item_id = f.id

        WHERE c.status = :status

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
        $sql = "
            SELECT
                c.*,
                u.fullname,
             u.email,
                l.item_name AS lost_item,
                f.item_name AS found_item

            FROM claims c

            INNER JOIN users u
                ON c.user_id=u.id

            INNER JOIN matches m
                ON c.match_id=m.id

            INNER JOIN lost_items l
                ON m.lost_item_id=l.id

            INNER JOIN found_items f
                ON m.found_item_id=f.id

            WHERE c.id=:id
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
            "SELECT match_id
             FROM claims
             WHERE id = :id"
        );

        $stmt->execute([
            ':id' => $id
        ]);

        $matchId = $stmt->fetchColumn();
        $stmt = $this->conn->prepare(
        "SELECT status
        FROM claims
         WHERE id = :id"
);

$stmt->execute([
    ':id' => $id
]);

$status = $stmt->fetchColumn();

if ($status !== 'pending') {
    throw new Exception("Claim has already been processed.");
}

        if (!$matchId) {
            throw new Exception("Claim not found.");
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
             SET status = 'claimed'
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
             SET status = 'claimed'
             WHERE id = :found_item_id"
        );

        $stmt->execute([
            ':found_item_id' => $match['found_item_id']
        ]);

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
            "UPDATE claims
             SET status='rejected'
             WHERE id=:id"
        );

        $stmt->execute([
            ':id'=>$id
        ]);

        $stmt = $this->conn->prepare(
            "UPDATE matches
             SET status='pending'
             WHERE id=(
                 SELECT match_id
                 FROM claims
                 WHERE id=:id
             )"
        );

        $stmt->execute([
            ':id'=>$id
        ]);

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
    $sql = "
        SELECT
            c.id,
            c.user_id,
            c.match_id,
            c.claim_message,
            c.status,
            c.created_at,

            l.item_name AS lost_item,
            f.item_name AS found_item

        FROM claims c

        INNER JOIN matches m
            ON c.match_id = m.id

        INNER JOIN lost_items l
            ON m.lost_item_id = l.id

        INNER JOIN found_items f
            ON m.found_item_id = f.id

        WHERE c.user_id = :user_id

        ORDER BY c.created_at DESC
    ";

    $stmt = $this->conn->prepare($sql);

    $stmt->execute([
        ':user_id' => $userId
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}