<?php
// Delete a routine
session_start();
header("Content-Type: application/json");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized"]);
    exit();
}

include_once '../../config/database.php';
include_once '../../models/routine.php';

// Get posted data
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->id)) {
    http_response_code(400);
    echo json_encode(["message" => "Missing routine ID"]);
    exit();
}

// Check if routine exists and belongs to user
$routine = new Routine();
$routine->id = $data->id;
$routine->user_id = $_SESSION['user_id'];

if ($routine->getOne()) {
    if ($routine->delete()) {
        http_response_code(200);
        echo json_encode(["message" => "Routine deleted successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Failed to delete routine"]);
    }
} else {
    http_response_code(404);
    echo json_encode(["message" => "Routine not found or not owned by user"]);
}
?>
