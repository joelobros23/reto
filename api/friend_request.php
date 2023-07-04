<?php
session_start();
require_once('../php/db_connect.php');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // Handle unauthorized access
    http_response_code(401);
    exit;
}

// Get the sender and receiver IDs from the request body
$requestData = json_decode(file_get_contents('php://input'), true);
$sender_id = $requestData['sender_id'];
$receiver_id = $requestData['receiver_id'];
$action = $requestData['action']; // Added action parameter

// Validate the sender and receiver IDs
if (empty($sender_id) || empty($receiver_id)) {
    // Handle invalid request
    http_response_code(400);
    exit;
}

// Check if the sender and receiver IDs belong to the logged-in user
if ($_SESSION['user_id'] != $sender_id || $_SESSION['user_id'] == $receiver_id) {
    // Handle unauthorized access
    http_response_code(401);
    exit;
}

// Check if a friend request already exists between the sender and receiver
if (friendRequestExists($sender_id, $receiver_id)) {
    // Handle case when a friend request already exists
    echo json_encode(['success' => false, 'message' => 'Friend request already sent']);
    exit;
}

// Send the friend request
if (sendFriendRequest($sender_id, $receiver_id, $action)) {
    // Record the action in the database
    recordAction($sender_id, $receiver_id, $action); // Pass the action as a parameter

    // Handle successful friend request
    $response = ['success' => true, 'message' => 'Friend request sent'];
} else {
    // Handle failed friend request
    $response = ['success' => false, 'message' => 'Failed to send friend request'];
}

// Encode the response as JSON
$jsonResponse = json_encode($response);

// Check for errors during JSON encoding
if ($jsonResponse === false) {
    $errorMessage = json_last_error_msg();
    echo "Error encoding JSON: " . $errorMessage;
} else {
    // Output the JSON response
    echo $jsonResponse;
}


/**
 * Checks if a friend request already exists between two users.
 *
 * @param int $sender_id
 * @param int $receiver_id
 * @return bool
 */
function friendRequestExists($sender_id, $receiver_id) {
    global $conn;

    $sql = "SELECT COUNT(*) AS count FROM friend_requests WHERE sender_id = ? AND receiver_id = ?";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $sender_id, $receiver_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    return ($row && $row['count'] > 0);
}

/**
 * Sends a friend request from the sender to the receiver.
 *
 * @param int $sender_id
 * @param int $receiver_id
 * @param string $action
 * @return bool
 */
function sendFriendRequest($sender_id, $receiver_id, $action) {
    global $conn;

    $sql = "INSERT INTO friend_requests (sender_id, receiver_id, action) VALUES (?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iis", $sender_id, $receiver_id, $action);

    return mysqli_stmt_execute($stmt);
}

/**
 * Records the action in the database.
 *
 * @param int $sender_id
 * @param int $receiver_id
 * @param string $action
 */
function recordAction($sender_id, $receiver_id, $action) {
    global $conn;

    $sql = "UPDATE friend_requests SET action = ? WHERE sender_id = ? AND receiver_id = ?";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sii", $action, $sender_id, $receiver_id);
    
    return mysqli_stmt_execute($stmt);
}


?>
