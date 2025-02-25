<?php
session_start();
header("Content-Type: application/json");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized"]);
    exit();
}

include_once '../../config/database.php';
include_once '../../models/destination.php';

// Get posted data
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->id)) {
    http_response_code(400);
    echo json_encode(["message" => "Missing destination ID"]);
    exit();
}

// Check if destination exists and belongs to user
$destination = new Destination();
$destination->id = $data->id;
$destination->user_id = $_SESSION['user_id'];

if ($destination->getOne()) {
    if ($destination->delete()) {
        http_response_code(200);
        echo json_encode(["message" => "Destination deleted successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Failed to delete destination"]);
    }
} else {
    http_response_code(404);
    echo json_encode(["message" => "Destination not found or not owned by user"]);
}
?>
