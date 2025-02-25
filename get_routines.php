<?php
// get_routines.php - Get all routines for the current user
session_start();
include 'config/database.php';
include 'models/routine.php';
include 'models/routine_activity.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit();
}

// Get routines
$routine = new Routine();
$result = $routine->getUserRoutines($_SESSION['user_id']);

if ($result->num_rows > 0) {
    $routines_arr = [];
    $activity = new RoutineActivity();
    
    while ($row = $result->fetch_assoc()) {
        $routine_item = [
            "id" => $row['id'],
            "name" => $row['name'],
            "created_at" => $row['created_at'],
            "activities" => []
        ];
        
        // Get activities for this routine
        $activities = $activity->getRoutineActivities($row['id']);
        
        while ($act_row = $activities->fetch_assoc()) {
            $routine_item["activities"][] = [
                "id" => $act_row['id'],
                "activity" => $act_row['activity'],
                "time" => $act_row['time_minutes'],
                "order" => $act_row['display_order']
            ];
        }
        
        $routines_arr[] = $routine_item;
    }
    
    echo json_encode($routines_arr);
} else {
    echo json_encode([]);
}
?>
