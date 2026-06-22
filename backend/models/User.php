<?php

class User
{
    private $conn;
    private $table = "users";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function findByEmail($email)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE email = :email
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute([
            ':email' => $email
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function register($fullname, $email, $password)
    {
        $sql = "INSERT INTO {$this->table}
                (
                    fullname,
                    email,
                    password
                )
                VALUES
                (
                    :fullname,
                    :email,
                    :password
                )";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':fullname' => $fullname,
            ':email'    => $email,
            ':password' => $password
        ]);
    }

    public function getById($id)
    {
        $sql = "SELECT *
                FROM {$this->table}
                WHERE id = :id
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute([
            ':id' => $id
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAll()
    {
        $sql = "SELECT *
                FROM {$this->table}
                ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
