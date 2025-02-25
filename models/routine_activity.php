<?php
require_once 'config/database.php';

class RoutineActivity {
    private $conn;
    private $table = "routine_activities";
    
    // Activity properties
    public $id;
    public $routine_id;
    public $activity;
    public $time_minutes;
    public $display_order;
    public $created_at;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function create() {
        $query = "INSERT INTO " . $this->table . " (routine_id, activity, time_minutes, display_order) 
                  VALUES (?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("isii", $this->routine_id, $this->activity, $this->time_minutes, $this->display_order);
        
        if($stmt->execute()) {
            $this->id = $this->conn->insert_id;
            return true;
        }
        return false;
    }
    
    public function getRoutineActivities($routine_id) {
        $query = "SELECT * FROM " . $this->table . " WHERE routine_id = ? ORDER BY display_order ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $routine_id);
        $stmt->execute();
        
        return $stmt->get_result();
    }
    
    public function update() {
        $query = "UPDATE " . $this->table . " SET activity = ?, time_minutes = ?, display_order = ? 
                  WHERE id = ? AND routine_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("siiii", $this->activity, $this->time_minutes, $this->display_order, $this->id, $this->routine_id);
        
        return $stmt->execute();
    }
    
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id = ? AND routine_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $this->id, $this->routine_id);
        
        return $stmt->execute();
    }
    
    public function deleteAllForRoutine($routine_id) {
        $query = "DELETE FROM " . $this->table . " WHERE routine_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $routine_id);
        
        return $stmt->execute();
    }
}
?>
