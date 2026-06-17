ge<?php

require_once __DIR__ . '/../config/database.php';

class MatchEngine
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function generateMatches()
    {
        $lostItems = $this->conn->query(
            "SELECT * FROM lost_items"
        )->fetchAll(PDO::FETCH_ASSOC);

        $foundItems = $this->conn->query(
            "SELECT * FROM found_items"
        )->fetchAll(PDO::FETCH_ASSOC);

        foreach($lostItems as $lost)
        {
            foreach($foundItems as $found)
            {
                $score = 0;

                if(
                    strtolower($lost['item_name']) ===
                    strtolower($found['item_name'])
                )
                {
                    $score += 60;
                }

                if(
                    strtolower($lost['category']) ===
                    strtolower($found['category'])
                )
                {
                    $score += 20;
                }

                similar_text(
                    strtolower($lost['description']),
                    strtolower($found['description']),
                    $similarity
                );

                $score += round($similarity * 0.2);

                if($score >= 70)
                {
                    $check = $this->conn->prepare(
                        "SELECT id
                         FROM matches
                         WHERE lost_item_id=:lost
                         AND found_item_id=:found"
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
                                confidence_score
                            )
                            VALUES
                            (
                                :lost,
                                :found,
                                :score
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
}

