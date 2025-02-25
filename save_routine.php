<?php
// save_routine.php - Save a routine to the database
session_start();
include 'config/database.php';
include 'models/routine.php';
include 'models/routine_activity.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = $_POST['name'] ?? '';
    $activities = json_decode($_POST['activities'] ?? '[]', true);
    
    if (empty($name) || empty($activities)) {
        echo json_encode(['success' => false, 'message' => 'Missing required data']);
        exit();
    }
    
    // Create routine
    $routine = new Routine();
    $routine->user_id = $_SESSION['user_id'];
    $routine->name = $name;
    
    if ($routine->create()) {
        // Add activities
        $activity = new RoutineActivity();
        $activity->routine_id = $routine->id;
        
        $success = true;
        $order = 1;
        
        foreach ($activities as $act) {
            $activity->activity = $act['activity'];
            $activity->time_minutes = $act['time'];
            $activity->display_order = $order++;
            
            if (!$activity->create()) {
                $success = false;
                break;
            }
        }
        
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Routine saved successfully']);
        } else {
            // If activities failed to create, delete the routine
            $routine->delete();
            echo json_encode(['success' => false, 'message' => 'Failed to save routine activities']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create routine']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
