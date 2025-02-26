<?php
// save_routine.php - Save a routine to the database
session_start();
include 'config/database.php';
include 'models/routine.php';
include 'models/routine_activity.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    // name = routinename, not username
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $activities = isset($_POST['activities']) ? json_decode($_POST['activities'], true) : [];

    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    if (empty($name) || empty($activities)) {
        echo json_encode(['success' => false, 'message' => 'Missing required data']);
        exit();
    }
    
    // Create routine
    $routine = new Routine();
    $routine->user_id = $_SESSION['user_id'];
    $routine->name = $name;
    
    if (!$user_id) {
        echo "Error: User ID not found.";
    }

    if ($name && !empty($activities) && $user_id) {
        // Insert routine into the 'routines' table
        echo "Name: " . var_export($name, true) . "<br>";
        echo "Activities: " . var_export($activities, true) . "<br>";
        echo "User ID: " . var_export($user_id, true) . "<br>";
        $stmt = $pdo->prepare("INSERT INTO routines (routine_name, user_id) VALUES (?, ?)");
        $stmt->execute([$name, $user_id]);
        $routine_id = $pdo->lastInsertId();  // Get the last inserted routine_id

        // Insert activities into the 'activities' table
        $stmt = $pdo->prepare("INSERT INTO activities (routine_id, activity_name, user_id) VALUES (?, ?, ?)");
        foreach ($activities as $activity) {
            $stmt->execute([$routine_id, $activity['name'], $user_id]);
        }

        // Respond with success
        echo json_encode(['success' => true]);
    } else {
        // Respond with an error
        echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    }
}
?>
