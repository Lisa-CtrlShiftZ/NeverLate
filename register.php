<?php
// register.php - Handle registration form submission
session_start();
include 'config/database.php';
include 'models/user.php';

$user = new User();

$database = new Database();
$conn = $database->getConnection();

$user->name = $_POST['name'] ?? '';
$user->email = $_POST['email'] ?? '';
$user->password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? ''; // Added fallback

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if passwords match
    if ($user->password !== $confirm_password) {
        echo json_encode(['error' => 'Passwords do not match']);
        exit();
    }
    
    // Check if email already exists
    $check_query = "SELECT * FROM users WHERE email = '$user->email'";
    $result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($result) > 0) {
        echo json_encode(['error' => 'Email already exists']);
        exit();
    }
    
    // Hash the password
    $hashed_password = password_hash($user->password, PASSWORD_DEFAULT);
    
    // Insert new user
    $insert_query = "INSERT INTO users (name, email, password, created_at) VALUES ('$user->name', '$user->email', '$hashed_password', NOW())";
    
    if (mysqli_query($conn, $insert_query)) {
        // Registration successful
        echo json_encode(['success' => 'Registration successful! You can now login.']);
        header("Location: index.html");
        exit();
    } else {
        // Registration failed
        echo json_encode(['error' => 'Registration failed: ' . mysqli_error($conn)]);
        exit();
    }
}

// If not a POST request, redirect to registration page
header("Location: index.html");
exit();
?>
