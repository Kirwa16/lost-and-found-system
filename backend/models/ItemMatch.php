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
        $sql = "INSERT INTO {$this->table}
                (lost_item_id, found_item_id, confidence_score, status)
                VALUES (:lost_item_id,:found_item_id,:confidence_score,'pending')";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':lost_item_id' => $lostItemId,
            ':found_item_id' => $foundItemId,
            ':confidence_score' => $confidenceScore
        ]);
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
        $stmt = $this->conn->prepare(
            "UPDATE matches SET status='approved' WHERE id=:id"
        );

        return $stmt->execute([':id'=>$id]);
    }

    public function rejectMatch(int $id): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE matches SET status='rejected' WHERE id=:id"
        );

        return $stmt->execute([':id'=>$id]);
    }

    public function deleteMatch(int $id): bool
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM matches WHERE id=:id"
        );

        return $stmt->execute([':id'=>$id]);
    }

    public function countPendingMatches(): int
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM matches WHERE status='pending'"
        );
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }
}
