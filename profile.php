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
    <title>Reto - Profile</title>
    <link rel="stylesheet" href="./css/styles.css">
    <link rel="stylesheet" href="./css/profile.css">
    <link rel="stylesheet" href="./css/button.css">
    <link rel="stylesheet" href="./css/particle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body onload="countFriendRequests()">

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
        <?php if ($user !== null) { ?>


            <!--  - - -- - - - - - --  - - - - -- -  - -- Profile Photo - - - -  - - -- - - - - - - - - -  -->
            <div class="profile-picture">
                <?php
                $query = "SELECT image_path FROM profile_pictures INNER JOIN users ON profile_pictures.user_id = users.id WHERE users.username = '$username'";
                $result = mysqli_query($conn, $query);

                if (mysqli_num_rows($result) > 0) {
                    $row = mysqli_fetch_assoc($result);
                    $imagePath = $row['image_path'];

                    // Generate a cache-busting query parameter
                    $cacheBuster = time();

                    // Check if the file exists
                    $imageFilePath = './' . $imagePath;
                    $imageFilePath = str_replace('../', '', $imageFilePath); // Remove double dots (../)

                    if (file_exists($imageFilePath)) {
                        echo '<img src="' . $imageFilePath . '?' . $cacheBuster . '" id="profilePicture" alt="Profile Picture">';
                    } else {
                        echo 'Profile picture file not found.';
                    }
                } else {
                    echo '<img src="./images/profile-photo.png" id="profilePicture" alt="">';
                }
                ?>
            </div>

            <!-- - - - - - -- - - - - - -  Edit Profile Picture button - - - - - - - - - - -- - - - - - -->
            <div style="height: 30px;">
            <div class="custom-file-input" <?php if ($user->canEditProfile()) { echo 'style="display: flex;"'; } else { echo 'style="display: none;"'; } ?>>
                <label for="file-input"><i class="fa-solid fa-pen-to-square fa-sm" style="color: #ffffff;" <?php if ($user->canEditProfile()) { echo 'style="display: flex;"'; } else { echo 'style="display: none;"'; } ?>></i></label>
                <input type="file" name="profile_picture" id="profilePicture" onchange="uploadProfilePicture(this)" <?php if ($user->canEditProfile()) { echo 'style="display: flex;"'; } else { echo 'style="display: none;"'; } ?>>
            </div>
            </div>


                            <!-- - - - - - -- - - - - --  - - -  Username Display - - - - - - - - - - - - - - -- - - -->
                            <div id="userName"><?php echo $user->getUsername(); ?></div>

            <div class="info">
                <!-- - - - - - -- - - - - --  - - - - - - - -  Bio - - - - - -- - - - - --  - - - -->
                <div style="display: flex; justify-content: center; align-items: center; width:90%;">
                <i style="position: relative; top: -10px; left: -5px;" class="fa-solid fa-quote-left"></i><p id="bio"></span><?php echo nl2br($user->getBio()); ?></p>
                </div>

                <!-- - - - - - -- - - - - -- - - -  - - - - - - Edit Bio - - - - - -- - - - - --  - - - -->
                <textarea id="bioTextarea" rows="3" maxlength="60" style="display: none;"><?php echo $user->getBio(); ?></textarea>
                <button id="editBioButton" onclick="editBio()" <?php if ($user->canEditProfile()) { echo 'style="display: flex;"'; } else { echo 'style="display: none;"'; } ?>>Edit Bio</button>
                <!-- - - - - - -- - - - - -- - - -  - - - - - - Save Button- - - - - -- - - - - --  - - - -->
                <button id="saveBioButton" onclick="saveBio()" style="display: none;">Save Bio</button>
                <hr <?php if ($user->canEditProfile()) { echo 'style="display: none;"'; } else { echo 'style="display: flex;"'; } ?>>
                <div style="display: flex; justify-content: center; align-items: center; width: 100%;">
                <ul>
                  <li><i class="fa-solid fa-cake-candles"></i><?php echo $user->getBirthdate(); ?> / Age: <?php echo $user->getAge(); ?></li>
                  <li><i class="fa-solid fa-venus-mars"></i><?php echo $user->getGender(); ?></li>
                  <li id="location"><i class="fa-sharp fa-solid fa-location-dot"></i><?php echo $user->getLocation(); ?></li>
                  <button id="getLocationButton" onclick="getLocation()" <?php if ($user->canEditProfile()) { echo 'style="display: flex;"'; } else { echo 'style="display: none;"'; } ?>>Get Location</button>
                </ul>
                </div>

                <script>
                  // Check the content of the third <li> element and toggle button visibility
                  var locationElement = document.getElementById('location');
                  var getLocationButton = document.getElementById('getLocationButton');
                  if (locationElement.textContent.trim() !== '') {
                    getLocationButton.style.display = 'none';
                  }
                </script>


                

            </div>
        <?php } else { ?>
            <div class="not-found">
                <p>Page Cannot Found.</p>
            </div>
        <?php } ?>
    </div>
    <div class="right-container">

    <div class="new-request">
        
        <?php
        // Retrieve the friend requests for the current user with "like" action
        $currentUser = $_SESSION['username'];
        $friendRequestsQuery = "SELECT fr.id, u.username AS sender_username, u.age, u.loc FROM friend_requests fr
                                INNER JOIN users u ON fr.sender_id = u.id
                                WHERE fr.receiver_id = (SELECT id FROM users WHERE username = '$currentUser')
                                AND fr.action = 'like'";
        $friendRequestsResult = mysqli_query($conn, $friendRequestsQuery);

        if ($friendRequestsResult && mysqli_num_rows($friendRequestsResult) > 0) {
            echo '<p id="request-label"><span id="friendRequestCount"></span> hearted you</p>';
            echo '<ul id="friendRequestList">';

            while ($row = mysqli_fetch_assoc($friendRequestsResult)) {
                $friendRequestId = $row['id'];
                $senderUsername = $row['sender_username'];
                $senderAge = $row['age'];
                $senderLocation = $row['loc'];
                $profilePicturePath = './images/profile-photo.png'; // Default profile picture path

                // Check if the user has a profile picture
                $profilePictureQuery = "SELECT image_path FROM profile_pictures INNER JOIN users ON profile_pictures.user_id = users.id WHERE users.username = '$senderUsername'";
                $profilePictureResult = mysqli_query($conn, $profilePictureQuery);

                if ($profilePictureResult && mysqli_num_rows($profilePictureResult) > 0) {
                    $profilePictureRow = mysqli_fetch_assoc($profilePictureResult);
                    $imagePath = $profilePictureRow['image_path'];

                    // Generate a cache-busting query parameter
                    $cacheBuster = time();

                    // Check if the file exists
                    $imageFilePath = './' . $imagePath;
                    $imageFilePath = str_replace('../', '', $imageFilePath); // Remove double dots (../)

                    if (file_exists($imageFilePath)) {
                        $profilePicturePath = $imageFilePath . '?' . $cacheBuster;
                    }
                }

                echo '<li>';

                echo '<div class="fr-profile-picture">';
                echo '<img src="' . $profilePicturePath . '" alt="Profile Picture">';
                echo '</div>';
                
                echo '<div class="profile-details">';
                echo '<span class="username">' . $senderUsername . '</span>';
                echo '<span class="age">' . $senderAge . ' years old</span>';
                echo '<span class="location"><i class="fa-sharp fa-solid fa-location-dot fa-sm"></i> ' . $senderLocation . '</span>';
                echo '</div>';

                echo '<div class="buttons">';
                echo '<button class="bubbly-button"  id="xbutton" onclick="declineFriendRequest(' . $friendRequestId . ', \'' . $senderUsername . '\')"><i class="fa-solid fa-xmark"></i></button>';
                echo '<button class="bubbly-button" id="heartbutton" onclick="acceptFriendRequest(' . $friendRequestId . ', \'' . $senderUsername . '\')"><i class="fa-solid fa-heart"></i></button>';
                echo '</div>';

                echo '</li>';
            }

            echo '</ul>';
        } else {
            echo '<p>No new friend requests.</p>';
        }
        ?>
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

<script src="./script/profile.js"></script>
<script src="./script/script.js"></script>

</body>
</html>
