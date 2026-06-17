<?php
class User {
    // Database connection and table name
    private $conn;
    private $table_name = "users";

    // Object properties (These fix the Intelephense errors)
    public $user_id;
    public $full_name;
    public $email;
    public $password;
    public $student_staff_id;
    public $phone_number;
    public $role;

    // Constructor with $db as database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Register user
    public function register() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    full_name = :full_name,
                    email = :email,
                    password_hash = :password_hash,
                    student_staff_id = :student_staff_id,
                    phone_number = :phone_number,
                    role = :role";

        $stmt = $this->conn->prepare($query);

        // Hash the password before saving
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);

        // Bind values
        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password_hash", $this->password);
        $stmt->bindParam(":student_staff_id", $this->student_staff_id);
        $stmt->bindParam(":phone_number", $this->phone_number);
        $stmt->bindParam(":role", $this->role);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Login user
    public function login() {
        $query = "SELECT user_id, full_name, email, password_hash, role
                  FROM " . $this->table_name . "
                  WHERE email = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->user_id = $row['user_id'];
            $this->full_name = $row['full_name'];
            $this->email = $row['email'];
            $this->role = $row['role'];

            if(password_verify($this->password, $row['password_hash'])) {
                return true;
            }
        }
        return false;
    }
}
?>