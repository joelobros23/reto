<?php
// Establish database connection
require_once('./db_connect.php');

// Check if the username already exists in the database
$username = $_POST["username"];
$sql = "SELECT COUNT(*) FROM users WHERE username = '$username'";
$result = mysqli_query($conn, $sql);
$count = mysqli_fetch_row($result)[0];

// Send the response indicating whether the username exists or not
if ($count > 0) {
    echo "exists";
} else {
    echo "available";
}

// Close database connection
mysqli_close($conn);
?>
