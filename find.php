<?php 
session_start();
require_once('./php/db_connect.php');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // Redirect to the login page or handle unauthorized access
    header("Location: ./php/logout");
    exit;
}

// Check if the user is verified
if (!isVerified($_SESSION['username'])) {
    // Redirect to the verification page or display an error message
    header("Location: ./php/verify");
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reto - Home</title>
    <link rel="stylesheet" href="./css/styles.css">
    <link rel="stylesheet" href="./css/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
<div id="burger-menu" onclick="toggleMenu()">
<label class="burger-icon">
  <span></span>
  <span></span>
  <span></span>
</label>
</div>

<nav id="navbar">
<div id="logo-holder">
    <img src="./images/logo.svg" alt="">
</div>
    <ul>
        <li><a href="./match"><i class="fa-solid fa-heart fa-sm"></i>‎ ‎<span>Match</span></a></li>
        <li><a href="./home"><i class="fa-solid fa-house fa-sm"></i>‎ ‎<span>Home </a></li>
        <li><a href="./<?php echo $_SESSION['username'] ?>"><i class="fa-solid fa-user fa-sm"></i>‎ ‎<span>Profile</span></a></li>
        <li><a href="./find"><i class="fa-solid fa-magnifying-glass fa-sm"></i>‎ ‎<span>Find</span></a></li>
        <li><a href="./messages"><i class="fa-solid fa-message fa-sm"></i>‎ ‎<span>Messages</span></a></li>
        <li><a href="./php/logout.php"><i class="fa-solid fa-sign-out-alt fa-sm"></i>‎ ‎<span>Logout</span></a></li>
    </ul>
    <div class="particles">
    <div class="purple"></div>
    <div class="medium-blue"></div>
    <div class="light-blue"></div>
    <div class="red"></div>
    <div class="orange"></div>
    <div class="yellow"></div>
    <div class="cyan"></div>
    <div class="light-green"></div>
    <div class="lime"></div>
    <div class="magenta"></div>
    <div class="lightish-red"></div>
    <div class="pink"></div>
</div>
</nav>
<div id="right-menu-button" onclick="toggleRightMenu()">
<ion-icon name="grid-outline"></ion-icon>
</div>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

    <div class="contents-container">
        
    </div>

    <script src="./script/script.js"></script>
</body>
</html>