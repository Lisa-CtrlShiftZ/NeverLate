<?php
// Update an existing routine
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

if (!isset($data->id) || !isset($data->name) || !isset($data->activities) || empty($data->activities)) {
    http_response_code(400);
    echo json_encode(["message" => "Missing required data"]);
    exit();
}

// Check if routine exists and belongs to user
$routine = new Routine();
$routine->id = $data->id;
$routine->user_id = $_SESSION['user_id'];

if ($routine->getOne()) {
    // Update routine name
    $routine->name = $data->name;
    
    if ($routine->update()) {
        // Delete existing activities and add new ones
        $activity = new RoutineActivity();
        if ($activity->deleteAllForRoutine($routine->id)) {
            $success = true;
            $order = 1;
            
            foreach ($data->activities as $act) {
                $activity->routine_id = $routine->id;
                $activity->activity = $act->activity;
                $activity->time_minutes = $act->time;
                $activity->display_order = $order++;
                
                if (!$activity->create()) {
                    $success = false;
                    break;
                }
            }
            
            if ($success) {
                http_response_code(200);
                echo json_encode(["message" => "Routine updated successfully"]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Failed to update routine activities"]);
            }
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failed to update routine activities"]);
        }
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Failed to update routine"]);
    }
} else {
    http_response_code(404);
    echo json_encode(["message" => "Routine not found or not owned by user"]);
}
?>
