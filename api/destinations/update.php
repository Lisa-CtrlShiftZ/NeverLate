<?php
// Update an existing destination
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

if (!isset($data->id) || !isset($data->name) || !isset($data->arrival_time) || 
    !isset($data->commute_time) || !isset($data->prep_buffer)) {
    http_response_code(400);
    echo json_encode(["message" => "Missing required data"]);
    exit();
}

// Check if destination exists and belongs to user
$destination = new Destination();
$destination->id = $data->id;
$destination->user_id = $_SESSION['user_id'];

if ($destination->getOne()) {
    // Update destination
    $destination->name = $data->name;
    $destination->arrival_time = $data->arrival_time;
    $destination->commute_time = $data->commute_time;
    $destination->prep_buffer = $data->prep_buffer;
    
    if ($destination->update()) {
        http_response_code(200);
        echo json_encode(["message" => "Destination updated successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Failed to update destination"]);
    }
} else {
    http_response_code(404);
    echo json_encode(["message" => "Destination not found or not owned by user"]);
}
?>
