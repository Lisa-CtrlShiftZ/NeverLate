<?php
require_once 'config/database.php';

class User {
    public $conn; 
    private $table = "users";
    
    // User properties
    public $id;
    public $name;
    public $email;
    public $password;
    public $status;
    public $date;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        if (!$this->conn) {
            throw new Exception("Database connection failed");
        }
    }

    public function create() {
        if($this->emailExists()) {
            return false;
        }

        $this->password = password_hash($this->password, PASSWORD_BCRYPT, ['cost' => 10]);
        
        $query = "INSERT INTO " . $this->table . " (name, email, password, status, date) 
                 VALUES (?, ?, ?, 1, NOW())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sss", $this->name, $this->email, $this->password);
        
        return $stmt->execute();
    }

    public function login() {
        $query = "SELECT * FROM " . $this->table . " WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $this->email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if(password_verify($this->password, $user['password'])) {
                return $user;
            }
        }
        return false;
    }

    public function emailExists() {
        $query = "SELECT id FROM " . $this->table . " WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $this->email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    public function getAll() {
        try {
            $query = "SELECT id, name, email, status, date FROM " . $this->table;
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $result = $stmt->get_result();

            if (!$result) {
                throw new Exception("Getting result set failed");
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("GetAll Error: " . $e->getMessage());
            return false;
        }
    }

    public function getOne() {
        $query = "SELECT id, name, email, status, date FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function update(): bool {
        $query = "UPDATE " . $this->table . " SET name = ?, email = ?, status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssii", $this->name, $this->email, $this->status, $this->id);
        return $stmt->execute();
    }

    public function delete(): bool {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->id);
        return $stmt->execute();
    }
}