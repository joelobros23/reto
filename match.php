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

// Retrieve the user's profile information from the session or database
$sql = "SELECT age, birthdate, gender, bio, loc FROM users WHERE username = '$username'";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    // Retrieve the profile information from the database
    $row = mysqli_fetch_assoc($result);
    $age = $row['age'];
    $birthdate = $row['birthdate'];
    $gender = $row['gender'];
    $bio = $row['bio'];
    $location = $row['loc'];

    if (empty($bio)) {
        $bio = "I need love";
    }

    // Check if the current user has the permission to edit the profile
    $canEditProfile = $username === $_SESSION['username']; // Adjust the condition based on your specific logic

    $user = new User($username, $age, $birthdate, $gender, $bio, $location, $canEditProfile);
} else {
    // Handle the case where the user profile is not found
    $user = null;
}

// Create User object
class User {
    private $username;
    private $age;
    private $birthdate;
    private $gender;
    private $bio;
    private $location;
    private $canEditProfile;

    public function __construct($username, $age, $birthdate, $gender, $bio, $location, $canEditProfile = false) {
        $this->username = $username;
        $this->age = $age;
        $this->birthdate = $birthdate;
        $this->gender = $gender;
        $this->bio = $bio;
        $this->location = $location;
        $this->canEditProfile = $canEditProfile;
    }

    public function canEditProfile() {
        return $this->canEditProfile;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getAge() {
        return $this->age;
    }

    public function getBirthdate() {
        return $this->birthdate;
    }

    public function getGender() {
        return $this->gender;
    }

    public function getBio() {
        return $this->bio;
    }

    public function getLocation() {
        return $this->location;
    }
}

function isVerified($username)
{
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
    <title>Reto - Match</title>
    <link rel="stylesheet" href="./css/styles.css">
    <link rel="stylesheet" href="./css/match.css">
    <link rel="stylesheet" href="./css/button.css">
    <link rel="stylesheet" href="./css/particle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>
    <div id="burger-menu" onclick="toggleMenu()">
        <label class="burger-icon"></label>
        <span></span>
        <span></span>
        <span></span>
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
    
    <div class="location-system">
    <div id="svg">
    <div id="animation"></div>
    </div>
        <p>We need to know your location first to find People near you</p>
    <button id="getLocationButton" onclick="getLocation()">Get Location</button>
    </div>

    <div id="searching-container">
    <div id="search-animate"></div>
    <p style="color: white;">Please wait while we are searching for People around. . .</p>
    </div>

        <div class="card">
            <div class="profile-picture">
                <img id="profile-picture" src="" alt="">
            </div>
            <span id="username"></span>
            <span>"‎‎<span id="bio"></span>‎‎"</span>

            <div style="display: flex; flex-direction: column;">
            <span>
                <i class="fa-solid fa-cake-candles"></i><span id="age"></span><span style="margin-left: 5px;">years old</span>
            </span>
            <span>
                <i class="fa-sharp fa-solid fa-location-dot"></i><div id="location"></div>
            </span>
            </div>

            <div class="buttons">
                <button class="bubbly-button" id="xbutton"><i class="fa-solid fa-xmark"></i></button>
                <button class="bubbly-button" id="heartbutton"><i class="fa-solid fa-heart"></i></button>
            </div>
        </div>

    </div>

<div class="right-container">

<!-- - - - - - LOCATION DISPLAY - - - - - - - -->
<div class="location-label">Dating-Location</div>
<div id="location-display"><i style="display: none;" id="loc-icon" class="fa-sharp fa-solid fa-location-dot"></i><?php echo $user->getLocation(); ?></div>

        <div class="options-container">
                <!-- Add a slider for age -->
                <div class="age-label">Age</div>
                <div class="age-container">
                    <label for="age-from"  style="margin-right: 10px;">From</label>
                    <input type="number" id="age-from" min="1" max="99" value="18">
        
                    <label for="age-to" style="margin-left: 20px; margin-right: 10px;">To</label>
                    <input type="number" id="age-to" min="1" max="99" value="50">
                </div>
                <!-- Add a dropdown list for gender -->
                
                <div class="gender-label">Gender</div>
                <div class="gender-container">
                
                <label>Male</label>
                  <input type="radio" name="gender" id="gender-radio-male" value="Male">
                
                  <label>Female</label>
                  <input type="radio" name="gender"  id="gender-radio-female" value="Female" checked>
                
                  <label>All</label>
                  <input type="radio" name="gender"  id="gender-radio-all" value="All">
                  
                </div>

                <button class="bubbly-button" id="search-apply-button">Apply</button>
        </div>
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
</div>

    <!-- This code block is to export the php User session to match.js-->
    <div id="user-id" data-user-id="<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : ''; ?>"></div>
    <!--  - - - -- - - - - - - - - - - - - - -- - - - - - - - - -- - - - -->
    <script src="./script/match.js"></script>
    <script src="./script/script.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.7.10/lottie_svg.min.js"></script>

    <script>
    // Replace the following URL with the URL of the LottieFiles animation JSON file
    var animationUrl = "https://assets7.lottiefiles.com/temp/lf20_JvT50n.json";
    
    var animationContainer = document.getElementById('animation');
    var anim = bodymovin.loadAnimation({
      container: animationContainer,
      renderer: 'svg',
      loop: true,
      autoplay: true,
      path: animationUrl
    });
  </script>

  <script>
    // Replace the following URL with the URL of the LottieFiles animation JSON file
    var animationUrl = "https://assets4.lottiefiles.com/packages/lf20_picwsjt3.json";
    
    var animationContainer = document.getElementById('search-animate');
    var anim = bodymovin.loadAnimation({
      container: animationContainer,
      renderer: 'svg',
      loop: true,
      autoplay: true,
      path: animationUrl
    });
  </script>
</body>
</html>
