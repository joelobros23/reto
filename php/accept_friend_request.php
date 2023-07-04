<?php
session_start();
require_once('db_connect.php');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // Redirect to the login page or handle unauthorized access
    header("Location: logout.php");
    exit;
}

// Check if the user is verified
if (!isVerified($_SESSION['username'])) {
    // Redirect to the verification page or display an error message
    header("Location: verify.php");
    exit;
}

// Check if the friend request ID is provided in the query parameter
if (!isset($_GET['id'])) {
    // Redirect or display an error message
    header("Location: error.php?message=Friend%20request%20ID%20is%20missing");
    exit;
}

// Retrieve the friend request ID from the query parameter
$requestId = $_GET['id'];

// Retrieve the user ID of the logged-in user
$loggedInUsername = $_SESSION['username'];
$loggedInUserId = getUserIdByUsername($loggedInUsername);

// Retrieve the sender and receiver IDs of the friend request
$friendRequestQuery = "SELECT sender_id, receiver_id FROM friend_requests WHERE id = $requestId";
$friendRequestResult = mysqli_query($conn, $friendRequestQuery);

if ($friendRequestResult && mysqli_num_rows($friendRequestResult) > 0) {
    $friendRequestRow = mysqli_fetch_assoc($friendRequestResult);
    $senderId = $friendRequestRow['sender_id'];
    $receiverId = $friendRequestRow['receiver_id'];

    // Check if the logged-in user is the receiver of the friend request
    if ($loggedInUserId === $receiverId) {
        // Accept the friend request and add the friendship to the friend_list table
        $acceptFriendQuery = "INSERT INTO friend_list (user_id, friend_id) VALUES ($receiverId, $senderId)";
        $acceptFriendResult = mysqli_query($conn, $acceptFriendQuery);

        // Add the vice versa entry to the friend_list table
        $acceptFriendViceVersaQuery = "INSERT INTO friend_list (user_id, friend_id) VALUES ($senderId, $receiverId)";
        $acceptFriendViceVersaResult = mysqli_query($conn, $acceptFriendViceVersaQuery);

        if (!$acceptFriendResult || !$acceptFriendViceVersaResult) {
            die("Insertion into friend_list failed: " . mysqli_error($conn));
        }

        // Delete the friend request from the friend_requests table
        $deleteRequestQuery = "DELETE FROM friend_requests WHERE id = $requestId";
        $deleteRequestResult = mysqli_query($conn, $deleteRequestQuery);

        if ($deleteRequestResult) {
            // Redirect or display a success message
            header("Location: ../profile.php");
            exit;
        } else {
            // Handle the case where the friend request deletion failed
            header("Location: error.php?message=Failed%20to%20delete%20friend%20request");
            exit;
        }
    } else {
        // Handle the case where the logged-in user is not the receiver of the friend request
        header("Location: error.php?message=User%20is%20not%20the%20receiver%20of%20the%20friend%20request");
        exit;
    }
} else {
    // Handle the case where the friend request is not found
    header("Location: error.php?message=Friend%20request%20not%20found");
    exit;
}

// Helper function to retrieve the user ID by username
function getUserIdByUsername($username) {
    global $conn;

    $query = "SELECT id FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['id'];
    }

    return null;
}

// Helper function to check if the user is verified
function isVerified($username) {
    global $conn;

    $query = "SELECT is_verified FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['is_verified'] == 1;
    }

    return false;
}
?>
