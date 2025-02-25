<?php
session_start();
header("Content-Type: application/json");

// Check if user is logged in
$logged_in = isset($_SESSION['user_id']);

echo json_encode([
    "logged_in" => $logged_in,
    "user" => $logged_in ? [
        "id" => $_SESSION['user_id'],
        "name" => $_SESSION['user_name'],
        "email" => $_SESSION['user_email']
    ] : null
]);
?>
