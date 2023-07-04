<?php
session_start();
require_once('db_connect.php');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // Redirect to the login page or handle unauthorized access
    header("Location: logout.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_SESSION['username'];
    $bio = $_POST['bio'];

    // Prepare the SQL statement with a placeholder for the bio value
    $sql = "UPDATE users SET bio = ? WHERE username = ?";

    // Create a prepared statement
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        // Bind the bio and username values to the prepared statement
        mysqli_stmt_bind_param($stmt, "ss", $bio, $username);

        // Execute the prepared statement
        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            // Bio saved successfully
            echo 'Bio saved';
        } else {
            // Error occurred while saving the bio
            echo 'Failed to save the bio';
        }

        // Close the prepared statement
        mysqli_stmt_close($stmt);
    } else {
        // Error occurred while preparing the statement
        echo 'Failed to prepare the statement';
    }
} else {
    // Invalid request method
    echo 'Invalid request';
}
?>
