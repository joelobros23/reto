<?php
session_start();
require_once('db_connect.php');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // Redirect to the login page or handle unauthorized access
    header("Location: ./logout");
    exit;
}

// Retrieve the friend request ID from the query parameter
$requestId = isset($_GET['id']) ? $_GET['id'] : null;

// Handle the decline action
if ($requestId !== null) {
    // Update the friend request with "dislike" action
    $currentUser = $_SESSION['username'];
    $declineQuery = "UPDATE friend_requests SET action = 'dislike' WHERE id = $requestId AND receiver_id = (SELECT id FROM users WHERE username = '$currentUser')";
    $declineResult = mysqli_query($conn, $declineQuery);

    if ($declineResult) {
        // Handle the success case (e.g., send a response back to the AJAX request)
        echo 'success';
    } else {
        // Handle any error cases
        echo 'failure';
    }
} else {
    // Handle invalid request (no friend request ID provided)
    echo 'invalid_request';
}
?>
