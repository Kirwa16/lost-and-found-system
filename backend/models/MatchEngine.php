<?php

require_once __DIR__ . '/../config/database.php';

class MatchEngine
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /*
    |--------------------------------------------------------------------------
    | Generate Matches
    |--------------------------------------------------------------------------
    */

    public function generateMatches()
    {
        $lostItems = $this->conn
            ->query("SELECT * FROM lost_items")
            ->fetchAll(PDO::FETCH_ASSOC);

        $foundItems = $this->conn
            ->query("SELECT * FROM found_items")
            ->fetchAll(PDO::FETCH_ASSOC);

        foreach($lostItems as $lost)
        {
            foreach($foundItems as $found)
            {
                $score = 0;

                if(
                    strtolower(trim($lost['item_name'])) ===
                    strtolower(trim($found['item_name']))
                )
                {
                    $score += 30;
                }

                if(
                    strtolower(trim($lost['category'])) ===
                    strtolower(trim($found['category']))
                )
                {
                    $score += 15;
                }

                if(
                    strtolower(trim($lost['color'])) ===
                    strtolower(trim($found['color']))
                )
                {
                    $score += 15;
                }

                if(
                    strtolower(trim($lost['brand_model'])) ===
                    strtolower(trim($found['brand_model']))
                )
                {
                    $score += 20;
                }

                similar_text(
                    strtolower($lost['unique_features'] ?? ''),
                    strtolower($found['unique_features'] ?? ''),
                    $featureSimilarity
                );

                $score += round($featureSimilarity * 0.1);

                similar_text(
                    strtolower($lost['description'] ?? ''),
                    strtolower($found['description'] ?? ''),
                    $descriptionSimilarity
                );

                $score += round($descriptionSimilarity * 0.1);

                if($score >= 70)
                {
                    $check = $this->conn->prepare(
                        "SELECT id
                         FROM matches
                         WHERE lost_item_id = :lost
                         AND found_item_id = :found"
                    );

                    $check->execute([
                        ':lost' => $lost['id'],
                        ':found' => $found['id']
                    ]);

                    if(!$check->fetch())
                    {
                        $stmt = $this->conn->prepare(
                            "INSERT INTO matches
                            (
                                lost_item_id,
                                found_item_id,
                                confidence_score,
                                status
                            )
                            VALUES
                            (
                                :lost,
                                :found,
                                :score,
                                'pending'
                            )"
                        );

                        $stmt->execute([
                            ':lost' => $lost['id'],
                            ':found' => $found['id'],
                            ':score' => $score
                        ]);
                    }
                }
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Get User Matches
    |--------------------------------------------------------------------------
    */

    public function getUserMatches($userId)
    {
        $stmt = $this->conn->prepare(
            "SELECT

                m.id,
                m.confidence_score,
                m.status,
                m.created_at,

                l.item_name AS lost_item,
                l.category,

                f.item_name AS found_item,
                f.location_found,
                f.date_found

             FROM matches m

             INNER JOIN lost_items l
                ON m.lost_item_id = l.id

             INNER JOIN found_items f
                ON m.found_item_id = f.id

             WHERE l.user_id = :user_id

             ORDER BY m.created_at DESC"
        );

        $stmt->execute([
            ':user_id' => $userId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
