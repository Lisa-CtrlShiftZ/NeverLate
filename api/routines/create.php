<?php
// Create a new routine
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
include_once '../../models/routine_activity.php';

// Get posted data
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->name) || !isset($data->activities) || empty($data->activities)) {
    http_response_code(400);
    echo json_encode(["message" => "Missing required data"]);
    exit();
}

// Create routine
$routine = new Routine();
$routine->user_id = $_SESSION['user_id'];
$routine->name = $data->name;

if ($routine->create()) {
    // Add activities
    $activity = new RoutineActivity();
    $activity->routine_id = $routine->id;
    
    $success = true;
    $order = 1;
    
    foreach ($data->activities as $act) {
        $activity->activity = $act->activity;
        $activity->time_minutes = $act->time;
        $activity->display_order = $order++;
        
        if (!$activity->create()) {
            $success = false;
            break;
        }
    }
    
    if ($success) {
        http_response_code(201);
        echo json_encode([
            "message" => "Routine created successfully",
            "id" => $routine->id
        ]);
    } else {
        // If activities failed to create, delete the routine
        $routine->delete();
        http_response_code(500);
        echo json_encode(["message" => "Failed to create routine activities"]);
    }
} else {
    http_response_code(500);
    echo json_encode(["message" => "Failed to create routine"]);
}
?>
