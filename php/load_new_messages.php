<?php
session_start();
require_once('db_connect.php');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
  // Redirect to the login page or handle unauthorized access
  header("Location: ./php/logout.php");
  exit;
}


function getUserIdByUsername($username) {
    global $conn;

    $sql = "SELECT id FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    if ($row) {
        return $row['id'];
    } else {
        echo 'Error: username Not found';
        return null;
    }
}

// Retrieve the username from the session
$username = $_SESSION['username'];

// Fetch the new messages for the current user using your existing logic
$loggedInUserId = getUserIdByUsername($username);
$sql = "SELECT * FROM messages WHERE recipient_id = ? AND is_read = 0";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $loggedInUserId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$messages = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Mark the fetched messages as read
$sql = "UPDATE messages SET is_read = 1 WHERE recipient_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $loggedInUserId);
mysqli_stmt_execute($stmt);

// Return the messages as JSON response
header('Content-Type: application/json');
echo json_encode($messages);
?>
