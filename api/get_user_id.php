<?php
session_start();
require_once('../php/db_connect.php');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // Redirect to the login page or handle unauthorized access
    header("Location: ../php/logout");
    exit;
}

// Check if the user is verified
if (!isVerified($_SESSION['username'])) {
    // Redirect to the verification page or display an error message
    header("Location: ../php/verify");
    exit;
}

// Retrieve the username from the query parameter
$username = isset($_GET['username']) ? $_GET['username'] : $_SESSION['username'];

// Retrieve the username of the current logged-in user
$loggedInUsername = $_SESSION['username'];

// Retrieve the ID of the current logged-in user from the users table
$loggedInUserId = getUserIdByUsername($loggedInUsername);

// Return the user ID in JSON format
echo json_encode(['id' => $loggedInUserId]);

// Helper function to get the user ID by username
function getUserIdByUsername($username) {
    global $conn;

    $sql = "SELECT id FROM users WHERE username = ?";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    return $row['id'];
}

function isVerified($username) {
    global $conn;

    $sql = "SELECT is_verified FROM users WHERE username = ?";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    return ($row && $row['is_verified'] == 1);
}
?>
