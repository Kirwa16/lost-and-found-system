<?php

class Database
{
    private $host = "localhost";
    private $dbname = "lost_and_found_db";
    private $username = "lost_admin";
    private $password = "LostFound2026";

    public function connect()
    {
        try {

            $pdo = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname}",
                $this->username,
                $this->password
            );

            $pdo->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );

            return $pdo;

        } catch(PDOException $e) {

            die("Database Error: " . $e->getMessage());
        }
    }
}