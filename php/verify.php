<?php
session_start();
require_once('../php/db_connect.php');
require '../PHPMailer-master/src/PHPMailer.php';
require '../PHPMailer-master/src/SMTP.php';
require '../PHPMailer-master/src/Exception.php';

// Declare the variable outside the conditional block
$userEmail = '';


// Retrieve the email from the URL parameter
if (isset($_GET['email'])) {
  $userEmail = $_GET['email'];

  // Use the email for sending the verification email
  // ...
} else {
  // Handle the case when the email parameter is not set
  $error = 'No email parameter found in the URL.';
}

function generateVerificationLink($email) {
    global $conn;
  
    // Generate a random token for verification
    $token = bin2hex(random_bytes(32));
  
    // Store the token in the verification_tokens table
    $insertSql = "INSERT INTO verification_tokens (email, verification_token) VALUES ('$email', '$token')";
    mysqli_query($conn, $insertSql);
  
    // Construct the verification link
    $verificationLink = "http://reto-date.com/php/verified.php?email=" . urlencode($email) . "&token=" . urlencode($token);
  
    return $verificationLink;
}

// Create a new PHPMailer instance
$mail = new PHPMailer\PHPMailer\PHPMailer();

// Configure SMTP settings
$mail->isSMTP();
$mail->Host = 'mail.joelolab.com';  // Replace with your SMTP server
$mail->SMTPAuth = true;
$mail->Username = 'developer@joelolab.com';  // Replace with your email address
$mail->Password = '09504727277superman';  // Replace with your email password
$mail->SMTPSecure = 'ssl';
$mail->Port = 465;

// Enable debug output and error logging
$mail->SMTPDebug = \PHPMailer\PHPMailer\SMTP::DEBUG_SERVER; // Enable verbose debug output
$mail->Debugoutput = function ($str, $level) {
  // Write the debug output to a log file
  file_put_contents('phpmailer.log', '[' . date('Y-m-d H:i:s') . '] ' . $str . PHP_EOL, FILE_APPEND);
};

// Compose the email
$mail->setFrom('developer@joelolab.com', 'Dating - Reto');  // Replace with your name and email address

if (isset($userEmail) && !empty($userEmail)) {
  $mail->addAddress($userEmail);  // Replace with the user's email address
  $mail->Subject = 'Email Verification';

  // Generate the verification link
  $verificationLink = generateVerificationLink($userEmail);

  // Construct the email body with the verification link
  $mail->Body = "Click the following link to verify your email: <a href=\"$verificationLink\">$verificationLink</a>";

  // Set the email format to HTML
  $mail->isHTML(true);

  // Send the email
  if ($mail->send()) {
    // Email sent successfully
  } else {
    // Failed to send email
    $error = 'Failed to send verification email';
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    echo 'Error: ' . $mail->ErrorInfo; // Display the specific error message from PHPMailer
  }
} else {
  // Handle the case when $userEmail is not set or empty
  $error = 'You must provide a valid recipient email address.';
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Email Verification</title>
  <link rel="stylesheet" href="./css/signup.css">
  <style>

body {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            background-color: #663399;
        }

        * {
            font-family: Arial, Helvetica, sans-serif;
        }

          img {
            height: 100%;
            width: 100%;
        }

        .icons {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            text-decoration: none;
            width: 100px;
            margin-right: 30px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2), 0 4px 8px rgba(0, 0, 0, 0.1);
            cursor: pointer;
        }

        .emailSent-container {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        align-items: center;
        width: 500px;
        height: 300px;
        background-color: #ffffff;
        margin-top: 10vh;
        padding: 20px;
        border-radius: 20px;
      }

              #email-sent {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            text-align: center;
            font-size: 30px;
            color: #663399;
        }
</style>
</head>
<body>
  <?php if (isset($error)) {
    echo '<p style="color: red;">' . $error . '</p>';
  } ?>


<div class="emailSent-container">
        <div id="email-sent">An email with a verification link has been sent to your Email Address.</div>
        <a href="https://mail.google.com/"><div class="icons"> <img src="../images/email-logos/gmail.svg" alt=""> </div></a>
        <a href="https://mail.yahoo.com"><div class="icons"> <img src="../images/email-logos/yahoo.svg" alt=""> </div></a>
        <a href="https://outlook.live.com/owa/"><div class="icons"> <img src="../images/email-logos/outlook.svg" alt=""> </div></a>
    </div>
</body>
</html>
