<?php
// Log an arrival (on time or late)
session_start();
header("Content-Type: application/json");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized"]);
    exit();
}

include_once '../../config/database.php';

// Get posted data
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->destination) || !isset($data->arrival_time) || !isset($data->on_time)) {
    http_response_code(400);
    echo json_encode(["message" => "Missing required data"]);
    exit();
}

// Create database connection
$database = new Database();
$conn = $database->getConnection();

// Insert arrival record
$query = "INSERT INTO arrivals (user_id, destination, arrival_time, on_time, created_at) 
          VALUES (?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($query);
$stmt->bind_param("issi", $_SESSION['user_id'], $data->destination, $data->arrival_time, $data->on_time);

if ($stmt->execute()) {
    http_response_code(201);
    echo json_encode(["message" => "Arrival logged successfully"]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Failed to log arrival"]);
}
?>
