<?php
session_start();
include 'config/database.php';
include 'models/user.php';

$user = new User();

$database = new Database();

$conn = $database->getConnection();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password']; // Don't escape password before verification
    
    // Check if email exists
    $sql = "SELECT user_id, name, email, password FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        
        // Verify password
        if (password_verify($password, $row['password'])) {
            // Password is correct, start a new session
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['user_name'] = $row['name'];
            $_SESSION['user_email'] = $row['email'];

            // Redirect to home page
            header("Location: index.html");
            exit();
        } else {
            // Password is incorrect
            echo json_encode(['error' => 'Invalid email or password']);
            exit();
        }
    } else {
        // Email doesn't exist
        echo json_encode(['error' => 'Invalid email or password']);
        exit();
    }
}

// If not a POST request, redirect to login page
header("Location: index.html");
exit();
?>
