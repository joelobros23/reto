<?php
require_once('db_connect.php');

// Get the email and token from the URL parameters
$email = $_GET['email'];
$token = isset($_GET['token']) ? $_GET['token'] : '';

// Check if the token is empty
if (!isset($token)) {
  echo '<h1>Verification</h1>';
  exit;
}

// Retrieve the user with the provided email and token from the verification_tokens table
$sql = "SELECT * FROM verification_tokens WHERE email = '$email' AND verification_token = '$token'";
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) == 1) {
    // Email and token combination is valid
    // Perform the necessary actions for successful verification

// Check if the email is already verified
$userSql = "SELECT * FROM users WHERE email = '$email' AND is_verified = 1";
$userResult = mysqli_query($conn, $userSql);

if (mysqli_num_rows($userResult) === 1) {
    echo 'Email is already verified. You can proceed to <a href="profile">login</a>.';
} else {
    // Update the user's verification status in the users table
    $updateSql = "UPDATE users SET is_verified = 1 WHERE email = ?";
    $stmt = mysqli_prepare($conn, $updateSql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);


    // Delete the verification record from the verification_tokens table
    $deleteSql = "DELETE FROM verification_tokens WHERE email = '$email'";
    mysqli_query($conn, $deleteSql);

    echo '
    <style>
        .messageBlocker {
          display: flex;
          flex-wrap: wrap;
          flex-direction: column;
          position: absolute;
          z-index: 99;
          width: 100%;
          height: 100vh;
          justify-content: center;
          align-items: center;
          background-color: #663399;
      }

        .message-container {
          display: flex;
          flex-wrap: wrap;
          flex-direction: column;
          position: absolute;
          z-index: 99;
          width: 500px;
          height: 400px;
          justify-content: center;
          align-items: center;
          background-color: #ffffff;
          border-radius: 40px;
      }
      
      .verified {
        text-align: center;
          display: flex;
          flex-wrap: wrap;
          font-size: 30px;
          color: #663399;
      }
      
      .message-container a {
          display: flex;
          flex-wrap: wrap;
          justify-content: center;
          align-items: center;
          text-align: center;
          font-size: 50px;
          color: #663399;
          text-decoration: none;
          background-color: rgb(255, 187, 0);
          font-size: 30px;
          padding: 20px;
          width: 120px;
          margin-top: 50px;
      }
    </style>

    <div class="messageBlocker">
    <div class="message-container">
        <div class="verified">Your account is now Verified<br>You can login now!</div>
        <a href="../login">LOGIN</a>
    </div>
    </div>';

}
}

?>
