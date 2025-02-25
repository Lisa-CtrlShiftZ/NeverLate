<?php
// delete_routine.php - Delete a routine
session_start();
include 'config/database.php';
include 'models/routine.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $id = $_POST['id'] ?? '';
    
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'Missing routine ID']);
        exit();
    }
    
    // Check if routine exists and belongs to user
    $routine = new Routine();
    $routine->id = $id;
    $routine->user_id = $_SESSION['user_id'];
    
    if ($routine->getOne()) {
        if ($routine->delete()) {
            echo json_encode(['success' => true, 'message' => 'Routine deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete routine']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Routine not found or not owned by user']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
