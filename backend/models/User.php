<?php

class User
{
    private $conn;
    private $table_name = "users";

    public $id;
    public $fullname;
    public $email;
    public $password;
    public $role;
    public $created_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /*
    |--------------------------------------------------------------------------
    | Register User
    |--------------------------------------------------------------------------
    */
    public function register()
    {
        if ($this->emailExists()) {
            return false;
        }

        $query = "INSERT INTO {$this->table_name}
                  (fullname, email, password, role)
                  VALUES
                  (:fullname, :email, :password, 'user')";

        $stmt = $this->conn->prepare($query);

        $hashedPassword = password_hash($this->password, PASSWORD_BCRYPT);

        $stmt->bindParam(':fullname', $this->fullname);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $hashedPassword);

        return $stmt->execute();
    }

    /*
    |--------------------------------------------------------------------------
    | Login User
    |--------------------------------------------------------------------------
    */
    public function login()
    {
        $query = "SELECT *
                  FROM {$this->table_name}
                  WHERE email = :email
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $this->email);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return false;
        }

        if (!password_verify($this->password, $user['password'])) {
            return false;
        }

        $this->id = $user['id'];
        $this->fullname = $user['fullname'];
        $this->email = $user['email'];
        $this->role = $user['role'];
        $this->created_at = $user['created_at'];

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Check Email Exists
    |--------------------------------------------------------------------------
    */
    public function emailExists()
    {
        $query = "SELECT id
                  FROM {$this->table_name}
                  WHERE email = :email
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $this->email);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    /*
    |--------------------------------------------------------------------------
    | Get User By ID
    |--------------------------------------------------------------------------
    */
    public function getUserById($id)
    {
        $query = "SELECT *
                  FROM {$this->table_name}
                  WHERE id = :id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /*
    |--------------------------------------------------------------------------
    | Get All Users
    |--------------------------------------------------------------------------
    */
    public function getAllUsers()
    {
        $query = "SELECT id,
                         fullname,
                         email,
                         role,
                         created_at
                  FROM {$this->table_name}
                  ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /*
    |--------------------------------------------------------------------------
    | Update Profile
    |--------------------------------------------------------------------------
    */
    public function updateProfile($id, $fullname, $email)
    {
        // Prevent duplicate email
        $query = "SELECT id
                  FROM {$this->table_name}
                  WHERE email = :email
                  AND id <> :id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->fetch()) {
            return false;
        }

        $query = "UPDATE {$this->table_name}
                  SET fullname = :fullname,
                      email = :email
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':fullname', $fullname);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /*
    |--------------------------------------------------------------------------
    | Verify Current Password
    |--------------------------------------------------------------------------
    */
    public function verifyPassword($id, $password)
    {
        $query = "SELECT password
                  FROM {$this->table_name}
                  WHERE id = :id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return false;
        }

        return password_verify($password, $user['password']);
    }

    /*
    |--------------------------------------------------------------------------
    | Change Password
    |--------------------------------------------------------------------------
    */
    public function changePassword($id, $password)
    {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $query = "UPDATE {$this->table_name}
                  SET password = :password
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /*
    |--------------------------------------------------------------------------
    | Update User (Admin)
    |--------------------------------------------------------------------------
    */
    public function updateUser($id, $fullname, $email, $role)
    {
        $query = "SELECT id
                  FROM {$this->table_name}
                  WHERE email = :email
                  AND id <> :id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->fetch()) {
            return false;
        }

        $query = "UPDATE {$this->table_name}
                  SET fullname = :fullname,
                      email = :email,
                      role = :role
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':fullname', $fullname);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /*
    |--------------------------------------------------------------------------
    | Update User Role
    |--------------------------------------------------------------------------
    */
    public function updateRole($id, $role)
    {
        $query = "UPDATE {$this->table_name}
                  SET role = :role
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /*
    |--------------------------------------------------------------------------
    | Delete User
    |--------------------------------------------------------------------------
    */
    public function deleteUser($id)
    {
        $query = "DELETE
                  FROM {$this->table_name}
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /*
    |--------------------------------------------------------------------------
    | Count Users
    |--------------------------------------------------------------------------
    */
    public function countUsers()
    {
        $query = "SELECT COUNT(*) AS total
                  FROM {$this->table_name}";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int) $row['total'];
    }

    /*
    |--------------------------------------------------------------------------
    | Search Users
    |--------------------------------------------------------------------------
    */
    public function searchUsers($keyword)
    {
        $keyword = "%{$keyword}%";

        $query = "SELECT id,
                         fullname,
                         email,
                         role,
                         created_at
                  FROM {$this->table_name}
                  WHERE fullname LIKE :keyword
                     OR email LIKE :keyword
                  ORDER BY fullname ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':keyword', $keyword);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}