<?php
// Create a new destination
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

if (!isset($data->name) || !isset($data->arrival_time) || !isset($data->commute_time) || !isset($data->prep_buffer)) {
    http_response_code(400);
    echo json_encode(["message" => "Missing required data"]);
    exit();
}

// Create destination
$destination = new Destination();
$destination->user_id = $_SESSION['user_id'];
$destination->name = $data->name;
$destination->arrival_time = $data->arrival_time;
$destination->commute_time = $data->commute_time;
$destination->prep_buffer = $data->prep_buffer;

if ($destination->create()) {
    http_response_code(201);
    echo json_encode([
        "message" => "Destination created successfully",
        "id" => $destination->id
    ]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Failed to create destination"]);
}
?>
