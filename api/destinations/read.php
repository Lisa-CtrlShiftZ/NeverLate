<?php
// Get destinations for the logged-in user
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

$destination = new Destination();
$result = $destination->getUserDestinations($_SESSION['user_id']);

if ($result->num_rows > 0) {
    $destinations_arr = [];
    
    while ($row = $result->fetch_assoc()) {
        $destinations_arr[] = [
            "id" => $row['id'],
            "name" => $row['name'],
            "arrival_time" => $row['arrival_time'],
            "commute_time" => $row['commute_time'],
            "prep_buffer" => $row['prep_buffer'],
            "created_at" => $row['created_at']
        ];
    }
    
    echo json_encode($destinations_arr);
} else {
    echo json_encode([]);
}
?>
