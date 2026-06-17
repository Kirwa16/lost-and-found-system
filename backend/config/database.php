<?php

class Database
{
    private $host = "127.0.0.1";
    private $db_name = "lost_and_found_db";
    private $username = "lost_admin";
    private $password = "LostFound2026";

    private $conn;

    public function connect()
    {
        if ($this->conn !== null) {
            return $this->conn;
        }

        try {

            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4",
                $this->username,
                $this->password
            );

            $this->conn->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );

            $this->conn->setAttribute(
                PDO::ATTR_DEFAULT_FETCH_MODE,
                PDO::FETCH_ASSOC
            );

        } catch (PDOException $e) {

            die(
                "Database Error: " .
                $e->getMessage()
            );
        }

        return $this->conn;
    }

    public function getConnection()
    {
        return $this->connect();
    }
}

