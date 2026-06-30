<?php
class User {
    private $conn;
    private $table_name = "users";

    // Object properties (matching your database)
    public $id;
    public $fullname;
    public $email;
    public $password;
    public $role;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

 // Register new user
public function register() {
    // First check if email exists
    if($this->emailExists()) {
        return false; // Email already exists
    }

    $query = "INSERT INTO " . $this->table_name . "
            SET
                fullname = :fullname,
                email = :email,
                password = :password,
                role = 'user'";

    $stmt = $this->conn->prepare($query);

    // Hash password
    $this->password = password_hash($this->password, PASSWORD_BCRYPT);

    $stmt->bindParam(":fullname", $this->fullname);
    $stmt->bindParam(":email", $this->email);
    $stmt->bindParam(":password", $this->password);

    try {
        if($stmt->execute()) {
            return true;
        }
        return false;
    } catch (PDOException $e) {
        // Catch duplicate entry error
        if ($e->getCode() == 23000) {
            return false; // Email already exists
        }
        throw $e; // Re-throw other errors
    }
}

    // Login user
    public function login() {
        $query = "SELECT id, fullname, email, password, role 
                  FROM " . $this->table_name . " 
                  WHERE email = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row && password_verify($this->password, $row['password'])) {
            $this->id = $row['id'];
            $this->fullname = $row['fullname'];
            $this->email = $row['email'];
            $this->role = $row['role'];
            return true;
        }
        return false;
    }

    // Check if email exists
    public function emailExists() {
        $query = "SELECT id, fullname, email, password, role 
                  FROM " . $this->table_name . " 
                  WHERE email = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();

        $num = $stmt->rowCount();
        if($num > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->fullname = $row['fullname'];
            $this->email = $row['email'];
            $this->role = $row['role'];
            return true;
        }
        return false;
    }
}
?>