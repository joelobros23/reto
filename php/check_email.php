<?php
require_once('./db_connect.php');

// Check if the email already exists in the database
$email = $_POST["email"];
$sql = "SELECT COUNT(*) FROM users WHERE email = '$email'";
$result = mysqli_query($conn, $sql);
$count = mysqli_fetch_row($result)[0];

// Send the response indicating whether the email exists or not
if ($count > 0) {
    echo "exists";
} else {
    echo "available";
}

// Close database connection
mysqli_close($conn);
?>
