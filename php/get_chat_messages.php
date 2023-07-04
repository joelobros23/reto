<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


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

// Function to get the chat messages between two users
function getChatMessages($loggedInUser, $otherUser) {
    global $conn;

    // Retrieve the chat messages from the database
    $sql = "SELECT sender_id, recipient_id, message FROM messages WHERE (sender_id = ? AND recipient_id = ?) OR (sender_id = ? AND recipient_id = ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iiii", $loggedInUser, $otherUser, $otherUser, $loggedInUser);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $chatMessages = array();

    // Debug message to check if the query is executed
    if (!$result) {
        echo 'Error executing query: ' . mysqli_error($conn);
        return $chatMessages;
    }

    // Debug message to check if any results are returned
    if (mysqli_num_rows($result) == 0) {
        echo 'No chat messages found.';
        return $chatMessages;
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $chatMessages[] = array(
            'sender_id' => $row['sender_id'],
            'recipient_id' => $row['recipient_id'],
            'message' => $row['message']
        );
    }

    return $chatMessages;
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



// Get the current chat user from the query parameter
$currentChatUser = isset($_GET['username']) ? $_GET['username'] : null;

// Check if the current chat user is valid
if ($currentChatUser) {
    // Get the ID of the logged-in user
    $loggedInUserId = getUserIdByUsername($_SESSION['username']);
    
    // Get the chat messages between the logged-in user and the current chat user
    $chatMessages = getChatMessages($loggedInUserId, getUserIdByUsername($currentChatUser));

    // Return the chat messages as JSON response
    header('Content-Type: application/json');
    echo json_encode($chatMessages);
} else {
    // Return an empty array if the current chat user is not valid
    echo json_encode(array());
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
