<?php

require_once __DIR__ . '/../config/database.php';

class User
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function register(
        $fullname,
        $email,
        $password
    )
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO users
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
            )"
        );

        return $stmt->execute([
            ':fullname' => $fullname,
            ':email' => $email,
            ':password' => $password
        ]);
    }

    public function findByEmail($email)
    {
        $stmt = $this->conn->prepare(
            "SELECT *
             FROM users
             WHERE email = :email
             LIMIT 1"
        );

        $stmt->execute([
            ':email' => $email
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserById($id)
    {
        $stmt = $this->conn->prepare(
            "SELECT *
             FROM users
             WHERE id = :id"
        );

        $stmt->execute([
            ':id' => $id
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateProfile(
        $id,
        $fullname,
        $email
    )
    {
        $stmt = $this->conn->prepare(
            "UPDATE users
             SET fullname = :fullname,
                 email = :email
             WHERE id = :id"
        );

        return $stmt->execute([
            ':fullname' => $fullname,
            ':email' => $email,
            ':id' => $id
        ]);
    }

    public function changePassword(
        $id,
        $password
    )
    {
        $hashedPassword =
            password_hash(
                $password,
                PASSWORD_DEFAULT
            );

        $stmt = $this->conn->prepare(
            "UPDATE users
             SET password = :password
             WHERE id = :id"
        );

        return $stmt->execute([
            ':password' => $hashedPassword,
            ':id' => $id
        ]);
    }
}

