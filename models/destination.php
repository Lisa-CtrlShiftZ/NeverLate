<?php
require_once 'config/database.php';

class Destination {
    private $conn;
    private $table = "destinations";
    
    // Destination properties
    public $id;
    public $user_id;
    public $name;
    public $arrival_time;
    public $commute_time;
    public $prep_buffer;
    public $created_at;
    public $updated_at;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function create() {
        $query = "INSERT INTO " . $this->table . " (user_id, name, arrival_time, commute_time, prep_buffer) 
                  VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("issii", $this->user_id, $this->name, $this->arrival_time, $this->commute_time, $this->prep_buffer);
        
        if($stmt->execute()) {
            $this->id = $this->conn->insert_id;
            return true;
        }
        return false;
    }
    
    public function getUserDestinations($user_id) {
        $query = "SELECT * FROM " . $this->table . " WHERE user_id = ? ORDER BY name ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        return $stmt->get_result();
    }
    
    public function getOne() {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ? AND user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $this->id, $this->user_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $this->id = $row['id'];
            $this->user_id = $row['user_id'];
            $this->name = $row['name'];
            $this->arrival_time = $row['arrival_time'];
            $this->commute_time = $row['commute_time'];
            $this->prep_buffer = $row['prep_buffer'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }
    
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET name = ?, arrival_time = ?, commute_time = ?, prep_buffer = ? 
                  WHERE id = ? AND user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssiiii", $this->name, $this->arrival_time, $this->commute_time, $this->prep_buffer, $this->id, $this->user_id);
        
        return $stmt->execute();
    }
    
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id = ? AND user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $this->id, $this->user_id);
        
        return $stmt->execute();
    }
}
?>
