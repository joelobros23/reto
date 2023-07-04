<?php
session_start();
require_once('./db_connect.php');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // Redirect to the login page or handle unauthorized access
    header("Location: login.php");
    exit;
}

// Check if the file was uploaded without errors
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['profile_picture'];

    // Specify the directory where you want to store the uploaded file
    $uploadDir = '../uploads/profile_pictures/';

    // Generate a unique file name
    $fileName = uniqid() . '_' . $file['name'];

    // Construct the path where the file will be stored
    $filePath = $uploadDir . $fileName;

    // Move the uploaded file to the desired location
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        // File upload successful
        // Update the user's profile picture path in the database
        $username = $_SESSION['username'];
        require_once('./db_connect.php');

        // Retrieve the user's ID from the users table
        $query = "SELECT id FROM users WHERE username = '$username'";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result);
        $userId = $row['id'];

        // Retrieve the old profile picture path from the profile_pictures table
        $query = "SELECT image_path FROM profile_pictures WHERE user_id = '$userId'";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result);
        $oldFilePath = $row['image_path'];

        // Delete the old profile picture file from the local folder if it exists
        if (file_exists($oldFilePath)) {
            unlink($oldFilePath);
        }

        // Update the user's profile picture path in the profile_pictures table
        $sql = "UPDATE profile_pictures SET image_path = '$filePath' WHERE user_id = '$userId'";
        mysqli_query($conn, $sql);

        // If no rows were affected, insert a new record in the profile_pictures table
        if (mysqli_affected_rows($conn) == 0) {
            $sql = "INSERT INTO profile_pictures (user_id, image_path) VALUES ('$userId', '$filePath')";
            mysqli_query($conn, $sql);
        }
    } else {
        // File upload failed
        echo 'Failed to move uploaded file.';
    }
} else {
    // Error occurred during file upload
    echo 'Error uploading file.';
}

// Redirect back to the profile page
header("Location: ../profile.php");
exit;
?>
