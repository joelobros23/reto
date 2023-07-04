
<?php
session_start();
require_once('./php/db_connect.php');

// Check if the user is already logged in, redirect to profile.php if true
if (isset($_SESSION['username'])) {
    header("Location: profile");
    exit;
}

// Check if the login form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate the login credentials
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Connect to the database
    require_once('./php/db_connect.php');

    // Prepare the SQL statement
    $query = "SELECT * FROM users WHERE username = '$username'";

    // Execute the query
    $result = mysqli_query($conn, $query);

    // Check if the username exists in the database
    if (mysqli_num_rows($result) === 1) {
        // Fetch the user row
        $user = mysqli_fetch_assoc($result);

        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Store the username and user ID in the session
            $_SESSION['username'] = $username;
            $_SESSION['user_id'] = $user['id']; // Assuming the user ID column is named 'id'
        
            // Redirect to the profile page
            header("Location: $username");
            exit;
        }
    }

    // Invalid login credentials
    $error = 'Invalid username or password';

    // Close the database connection
    mysqli_close($conn);
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RETO DATING</title>
    <link rel="stylesheet" href="./css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Play&display=swap" rel="stylesheet">

</head>
<body>
<div id="loading-screen">
  <div class="loader"></div>
  <div>
  <img src="./images/logo2.svg" alt="">
  </div>
</div>

<div id="menu">
    <div class="logo-container">
        <img src="./images/logo2.svg" alt="">
    </div>
  <div id="menu-bar" onclick="menuOnClick()">
    <div id="bar1" class="bar"></div>
    <div id="bar2" class="bar"></div>
    <div id="bar3" class="bar"></div>
  </div>
  <nav class="nav" id="nav">
    <ul>
      <li><a href="#">Home</a></li>
      <li><a href="#">About</a></li>
      <li><a href="#">Contact</a></li>
    </ul>
  </nav> 
</div>

<div class="menu-bg" id="menu-bg"></div>

    <section>
        <div class="tagline-container">
            <div id="tagline">
            Start Dating & Searching for <br> Friends or True Love in Reto
            </div>
            
            <div id="match-image">
                <img src="images/match.svg" alt="">
            </div>
        </div>

  <div class="main">
    
  <div class="community">
  <a href="signup.php"><button>SIGN-UP</button></a>
    <img src="./images/community.svg" alt="">
  </div>

	</div>
    </section>

    <section class="second-section">
      <div class="dd">
      <div class="locate-people">
        <img src="images/locate-people.svg" alt="">
      </div>
      <div class="details">
<h1 style="color: rgb(131, 36, 255);">Locate People Around you</h1>
<p style="text-align: center; font-family: sans-serif;">Here in Reto, We have an advance system  <br> to locate or navigate people near you</p>
      </div>
    </div>
    </section>

    <section class="third-section">
      <div class="dd">
        <div class="locate-people">
          <img src="images/find-friends.svg" alt="">
        </div>
        <div class="details-2">
  <h1 style="color: rgb(131, 36, 255);">Find New friends in Reto</h1>
  <p style="text-align: center; font-family: sans-serif;">Discover a vibrant community where genuine connections thrive, as our dating app opens doors to new horizons, empowering you to forge meaningful friendships that transcend boundaries and ignite unforgettable adventures. Uncover the joy of expanding your social circle, as you meet kindred spirits who share your passions, exchange laughter, and create lifelong memories. With our intuitive platform, connecting with like-minded individuals who are also seeking genuine friendships has never been easier.</p>
        </div>
      </div>
    </section>

    <script src="script/script.js"></script>
    <script>
      window.addEventListener('load', function() {
      var loadingScreen = document.getElementById('loading-screen');
      loadingScreen.style.display = 'none';
    });

        function menuOnClick() {
          document.getElementById("menu-bar").classList.toggle("change");
          document.getElementById("nav").classList.toggle("change");
          document.getElementById("menu-bg").classList.toggle("change-bg");
        }
    </script>
<footer>
<div class="footer">
<div class="row">
<a href="#"><i class="fa fa-facebook"></i></a>
<a href="#"><i class="fa fa-instagram"></i></a>
<a href="#"><i class="fa fa-youtube"></i></a>
<a href="#"><i class="fa fa-twitter"></i></a>
</div>

<div class="row">
<ul>
<li><a href="#">Contact us</a></li>
<li><a href="#">Our Services</a></li>
<li><a href="#">Privacy Policy</a></li>
<li><a href="#">Terms & Conditions</a></li>
<li><a href="#">Career</a></li>
</ul>
</div>

<div class="row">
RETO Copyright © 2021 RETO-DATE - All rights reserved || Designed By: JOELO
</div>
</div>
</footer>

</body>
</html>