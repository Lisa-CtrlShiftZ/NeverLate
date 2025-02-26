<?php
class Database {
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "neverlate"; 
    private $conn; 

    public function getConnection()
    {
        $this->conn = null;
        try {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database);
            $this->conn->set_charset("utf8mb4");
            return $this->conn;
        } catch (Exception $e) {
            error_log("Connection error: " . $e->getMessage());
            die("Connection failed: " . $e->getMessage());
        }
    }
}
