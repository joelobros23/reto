<?php
session_start();
require_once('./db_connect.php');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
  // Redirect to the login page or handle unauthorized access
  header("Location: ./logout.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_SESSION['username'];
  $location = $_POST['location'];
  $latitude = $_POST['latitude'];
  $longitude = $_POST['longitude'];

  // Update the location and city (or town) value in the database
  $sql = "UPDATE users SET loc = '$location' WHERE username = '$username'";
  $result = mysqli_query($conn, $sql);

  if ($result) {
    // Location and city (or town) saved successfully
    echo 'Location and city (or town) saved';
  } else {
    // Error occurred while saving the location and city (or town)
    echo 'Failed to save the location and city (or town)';
  }
} else {
  // Invalid request method
  echo 'Invalid request';
}
?>
