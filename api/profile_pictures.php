<?php
// Import necessary files and establish database connection
require_once('../php/db_connect.php');

// Retrieve the user_id parameter from the request
$user_id = $_GET['user_id'];

// Prepare the SQL statement
$sql = "SELECT user_id, image_path FROM profile_pictures WHERE user_id = ?";

// Prepare the statement and bind parameters
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $user_id);

// Execute the statement
mysqli_stmt_execute($stmt);

// Get the result
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

// Check if a profile picture is found
if ($row) {
    // Profile picture found, return the user_id and image_path in a JSON response
    $response = array('success' => true, 'user_id' => $row['user_id'], 'image_path' => $row['image_path']);
} else {
    // No profile picture found, return a default image or appropriate response
    $response = array('success' => false, 'message' => 'No profile picture found');
}

// Close the database connection
mysqli_stmt_close($stmt);
mysqli_close($conn);

// Return the JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
