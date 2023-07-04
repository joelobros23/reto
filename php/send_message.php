<?php
session_start();
require_once('db_connect.php');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // Redirect to the login page or handle unauthorized access
    header("Location: ./php/logout");
    exit;
}

// Check if the user is verified
if (!isVerified($_SESSION['username'])) {
    // Redirect to the verification page or display an error message
    header("Location: ./verify");
    exit;
}

// Retrieve the username from the query parameter
$username = isset($_GET['username']) ? $_GET['username'] : $_SESSION['username'];

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

// Get the logged-in user's ID
$loggedInUser = getUserByUsername($_SESSION['username']);
$loggedInUserId = $loggedInUser['id'];

// Retrieve the message and recipient from the request body
$data = json_decode(file_get_contents('php://input'), true);
$message = $data['message'];
$recipient = $data['recipient'];

// Get the recipient's ID
$recipientUser = getUserByUsername($recipient);
$recipientUserId = $recipientUser['id'];

// Echo the value
echo $recipientUserId;

// Save the message in the database
saveMessage($loggedInUserId, $recipientUserId, $message);

function getUserByUsername($username) {
    global $conn;

    $sql = "SELECT * FROM users WHERE username = ?";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    return $row;
}

// In send_message.php
$response = array(
    'success' => true,
    'message' => 'Message sent successfully'
);

header('Content-Type: application/json');
echo json_encode($response);


function saveMessage($loggedInUserId, $recipientId, $message) {
    global $conn;

    $sql = "INSERT INTO messages (sender_id, recipient_id, message) VALUES (?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iis", $loggedInUserId, $recipientId, $message);
    mysqli_stmt_execute($stmt);
}


?>
