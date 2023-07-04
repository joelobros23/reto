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
    header("Location: ./verify");
    exit;
}

// Retrieve the username from the query parameter
$username = isset($_GET['username']) ? $_GET['username'] : $_SESSION['username'];

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

function getFriendList($username)
{
    global $conn;

    // Get the ID of the current logged-in user
    $loggedInUserId = getUserIdByUsername($_SESSION['username']);

    $sql = "SELECT friend_id FROM friend_list WHERE user_id = ?";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $loggedInUserId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $friendList = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $friendId = $row['friend_id'];

        // Fetch the username directly from the users table
        $sql = "SELECT username FROM users WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $friendId);
        mysqli_stmt_execute($stmt);
        $userResult = mysqli_stmt_get_result($stmt);
        $userData = mysqli_fetch_assoc($userResult);

        if ($userData) {
            $friendList[] = $userData['username'];
        }
    }

    return $friendList;
}

// Helper function to get the user ID by username
function getUserIdByUsername($username)
{
    global $conn;

    $sql = "SELECT id FROM users WHERE username = ?";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    return $row['id'];
}

function getUserByUsername($username)
{
    global $conn;

    $sql = "SELECT * FROM users WHERE username = ?";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    return $row;
}

function getProfilePictureByUserId($userId)
{
    global $conn;

    $sql = "SELECT image_path FROM profile_pictures WHERE user_id = ?";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    if ($row && $row['image_path'] !== null) {
        $imagePath = $row['image_path'];
        $imagePath = ltrim($imagePath, './'); // Remove the two dots at the beginning
    } else {
        $imagePath = "./images/profile-photo.png"; // Assign the default profile picture path
    }

    // Check if the file does not exist
    if (!file_exists($imagePath)) {
        $imagePath = "./images/profile-photo.png"; // Assign the default profile picture path
    }

    return $imagePath;
}

// Get the friend list for the current user
$friendList = getFriendList($_SESSION['username']);

// Fetch the inbox messages for each friend in the friend list
$inboxMessages = array();
foreach ($friendList as $friendUsername) {
    $inboxMessage = array();
    $inboxMessage['sender_name'] = $friendUsername;
    $inboxMessage['recipient_name'] = $_SESSION['username'];

    // Retrieve the latest message for this friend
    $sql = "SELECT * FROM messages WHERE (sender_id = ? AND recipient_id = ?) OR (sender_id = ? AND recipient_id = ?) ORDER BY created_at DESC LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    $loggedInUserId = getUserIdByUsername($_SESSION['username']);
    $friendId = getUserIdByUsername($friendUsername);
    mysqli_stmt_bind_param($stmt, "iiii", $loggedInUserId, $friendId, $friendId, $loggedInUserId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    if ($row) {
        $inboxMessage['message_preview'] = $row['message'];
        $inboxMessage['timestamp'] = $row['created_at'];

        // Count the unread messages
        $sql = "SELECT COUNT(*) AS unread_count FROM messages WHERE sender_id = ? AND recipient_id = ? AND is_read = 0";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $friendId, $loggedInUserId);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $unreadCountRow = mysqli_fetch_assoc($result);
        $inboxMessage['unread_count'] = $unreadCountRow['unread_count'];
    } else {
        // If there are no messages, set default values
        $inboxMessage['message_preview'] = "";
        $inboxMessage['timestamp'] = "";
        $inboxMessage['unread_count'] = 0;
    }

    $inboxMessages[] = $inboxMessage;
}

// Sort the $inboxMessages array in descending order based on the timestamp
usort($inboxMessages, function ($a, $b) {
    return strtotime($b['timestamp']) - strtotime($a['timestamp']);
});


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reto - Home</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="./css/messages.css">
    <link rel="stylesheet" href="./css/particle.css">
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


            <div class="friend-list">
                        <div class="new-matches-label">New Matches</div>
                        <ul>
                            <?php
                                // Get the friend list of the logged-in user
                                $friendList = getFriendList($username);

                                // Loop through the friend list and display each friend
                                foreach ($friendList as $friendUsername) {
                                  $friend = getUserByUsername($friendUsername);
                                  $friendName = $friend['name'];
                                  $friendProfilePicture = getProfilePictureByUserId($friend['id']);
                                  $profilePageUrl = 'http://reto-date.com/' . $friendUsername;
                                  ?>
                                    <li>
                                      <div class="friend-profile-pic">
                                        <a href="<?php echo $profilePageUrl; ?>"><img src="<?php echo $friendProfilePicture; ?>" alt="Profile Picture"></a>
                                      </div>
                                      <div class="friend-details">
                                        <a href="<?php echo $profilePageUrl; ?>"><span class="friend-username"><?php echo $friendUsername; ?></span></a>
                                      </div>
                                      <button class="chat-btn" onclick="openChat('<?php echo $friendUsername; ?>')">Chat</button>
                                    </li>
                                  <?php
                                }
                                      ?>
                            </ul>


            </div>

    <h2 style="color:white;">Inbox</h2>

<?php if (empty($inboxMessages)): ?>
    <p>No messages in the inbox.</p>
<?php else: ?>

    <ul class='inbox-container'>
    <?php
    // Sort the $inboxMessages array in descending order based on the timestamp
    usort($inboxMessages, function ($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });

    foreach ($inboxMessages as $message):
        if (!empty($message['message_preview'])):
            ?>
            <li style="list-style-type: none;" onclick="openChat('<?php echo $message['sender_name']; ?>')">
            <?php $recipientProfilePicture = getProfilePictureByUserId(getUserIdByUsername($message['sender_name'])); ?>

            <div class="inbox-chat-container" onclick="toggleRightMenu()">
                <div class="message-sender-profile-picture">
                  <img src="<?php echo $recipientProfilePicture; ?>" alt="Profile Picture">
                </div>
                    <div class="chat-details">
                        <div><?php echo $message['sender_name']; ?></div>
                        <div><?php echo strlen($message['message_preview']) > 15 ? substr($message['message_preview'], 0, 15) . '. . .' : $message['message_preview']; ?></div>
                        <div><?php echo $message['timestamp']; ?></div>
                     </div>
            </div>


            </li>
        <?php endif; ?>
    <?php endforeach; ?>
</ul>

<?php endif; ?>


</div>

<div class="right-container">
    <div id="chat-box">
        <div id="chat-title"></div>
        <div id="chat-messages"></div>
        <form id="chat-form" onsubmit="sendMessage(event)">
          <div id="chat-input-container">
            <div id="option-buttons">
            <button type="button"><i class="fa-solid fa-microphone fa-lg"></i></button>
            <button type="button"><i class="fa-solid fa-smile fa-xl"></i></button>
            <button type="button"><i class="fa-solid fa-image fa-lg"></i></button>
            <button type="button"><i class="fa-solid fa-paperclip fa-lg"></i></button>
            <button type="button"><i class="fa-solid fa-camera fa-lg"></i></button>
            </div>
            <input type="text" id="message-input" placeholder="Type your message...">
            <button type="submit" id="send-button"><ion-icon name="send"></ion-icon></button>
          </div>
        </form>

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
<script src="./script/script.js"></script>
<script>
function loadNewMessages() {
  var xhr = new XMLHttpRequest();
  xhr.onreadystatechange = function() {
    if (xhr.readyState === XMLHttpRequest.DONE) {
      if (xhr.status === 200) {
        var response = JSON.parse(xhr.responseText);
        // Handle the response and update the chat messages
        // For example, you can append new messages to the chat window
        response.forEach(function(message) {
          var senderUsername = message.sender_id === loggedInUserId ? 'You' : currentChatUser;
          if (senderUsername === 'You') {
            senderUsername = 'You'; // Use the logged-in username instead of 'You'
          }

          // Create a container div for the message
          var messageContainer = document.createElement('div');

          // Add a CSS class to the message container based on the sender's ID
          messageContainer.classList.add(message.sender_id === loggedInUserId ? 'message-sender' : 'message-recipient');

          // Create a profile picture element
          var profilePicture = document.createElement('img');
          fetchProfilePicture(message.sender_id)
            .then(function(profilePictureUrl) {
              profilePicture.src = profilePictureUrl; // Set the profile picture URL
            });

          profilePicture.alt = 'Profile Picture';
          profilePicture.classList.add('profile-picture');

          // Create a message element
          var messageElement = document.createElement('div');
          messageElement.textContent = message.message;
          messageElement.classList.add('message');

          // Append the profile picture and message elements to the container div
          if (message.sender_id === loggedInUserId) {
            messageContainer.appendChild(messageElement);
            messageContainer.appendChild(profilePicture);
          } else {
            messageContainer.appendChild(profilePicture);
            messageContainer.appendChild(messageElement);
          }

          // Append the container div to the chat messages
          var chatMessages = document.getElementById('chat-messages');
          chatMessages.appendChild(messageContainer);

          chatMessages.scrollTop = chatMessages.scrollHeight; // Scroll to the bottom of the chat messages
        });
      } else {
        console.error('Error loading new messages:', xhr.status);
      }
    }
  };

  // Send a GET request to your server endpoint to fetch new messages
  xhr.open('GET', './php/load_new_messages.php', true);
  xhr.send();
}

setInterval(loadNewMessages, 1000);
setInterval(loadInitialChat, 1000);
</script>
<script>
    var friendList = null;
    var currentChatUser = null;
    var friendList = [];
    var chatBox = document.getElementById('chat-box');
    var chatTitle = document.getElementById('chat-title');
    var chatMessages = document.getElementById('chat-messages');
    var messageInput = document.getElementById('message-input');
    var loggedInUserId; // Declare loggedInUserId as a global variable
    

    // Open the chat box and load the messages
    function openChat(friendUsername) {
        currentChatUser = friendUsername;
        saveCurrentChatUser(friendUsername); // Save the current chat user's username to local storage
        chatBox.style.display = 'block';
        loadInitialChat();
        loadChatMessages();
    }

 // Fetch profile picture by user ID
function fetchProfilePicture(userId) {
  return fetch('http://reto-date.com/api/profile_pictures.php?user_id=' + userId)
    .then(response => response.json())
    .then(data => {
        var image_path = data.image_path || ''; // Set a default value if the image_path is undefined
        if (image_path.startsWith('../')) {
          image_path = image_path.substring(3); // Remove the two dots
        }
        return image_path;
    })
    .catch(error => {
      console.error('Error fetching profile picture:', error);
      // Handle the error gracefully, such as displaying a default profile picture
      return './images/profile-photo.png';
    });
}

// Load and display chat messages with profile pictures
function loadChatMessages() {
  // Clear the existing messages
  chatMessages.innerHTML = '';

  // Make an AJAX request to fetch the chat messages
  var xhr = new XMLHttpRequest();
  xhr.open('GET', './php/get_chat_messages.php?username=' + currentChatUser, true);
  xhr.onreadystatechange = function() {
    if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
      // Parse the JSON response
      var response = JSON.parse(xhr.responseText);

      // Loop through the chat messages and create the message elements
      response.forEach(function(message) {
        var senderUsername = message.sender_id === loggedInUserId ? 'You' : currentChatUser;
        if (senderUsername === 'You') {
          senderUsername = 'You'; // Use the logged-in username instead of 'You'
        }

        // Create a container div for the message
        var messageContainer = document.createElement('div');

        // Add a CSS class to the message container based on the sender's ID
        messageContainer.classList.add(message.sender_id === loggedInUserId ? 'message-sender' : 'message-recipient');

        // Create a profile picture element
        var profilePicture = document.createElement('img');
        fetchProfilePicture(message.sender_id)
          .then(function(profilePictureUrl) {
            profilePicture.src = profilePictureUrl; // Set the profile picture URL
          });

        profilePicture.alt = 'Profile Picture';
        profilePicture.classList.add('profile-picture');

        // Create a message element
        var messageElement = document.createElement('div');
        messageElement.textContent = message.message;
        messageElement.classList.add('message');

        // Append the profile picture and message elements to the container div
        if (message.sender_id === loggedInUserId) {
          messageContainer.appendChild(messageElement);
          messageContainer.appendChild(profilePicture);
        } else {
          messageContainer.appendChild(profilePicture);
          messageContainer.appendChild(messageElement);
        }

        // Append the container div to the chat messages
        chatMessages.appendChild(messageContainer);
      });

      chatMessages.scrollTop = chatMessages.scrollHeight; // Scroll to the bottom of the chat messages
    }
  };
  xhr.send();
}

// Fetch profile picture and load chat messages
fetchProfilePicture(loggedInUserId)
  .then(profilePictureUrl => {
    var profilePicture = document.getElementById('profile-picture');
    profilePicture.src = profilePictureUrl; // Set the logged-in user's profile picture
    loadChatMessages(); // Load and display chat messages
  })
  .catch(error => {
    console.error('Error loading profile picture:', error);
    // Handle the error gracefully, such as displaying a default profile picture
    var profilePicture = document.getElementById('profile-picture');
    profilePicture.src = 'path/to/default/profile_picture.jpg';
    loadChatMessages(); // Load and display chat messages
  });




    function saveCurrentChatUser(username) {
        localStorage.setItem('currentChatUser', username);
    }

    // Load the initial chat messages when the page is loaded or when the chat box is opened
    function loadInitialChat() {
        var savedChatUser = localStorage.getItem('currentChatUser');
        // Check if there is a current chat user
        if (savedChatUser) {
            currentChatUser = savedChatUser;
            if (currentChatUser) {
                // Make an AJAX request or use any other method to retrieve the ID and username of the current logged-in user
                fetch('./api/get_user_id.php')
                    .then(response => response.json())
                    .then(data => {
                        loggedInUserId = data.id; // Assign the response value to the global variable loggedInUserId

                        chatTitle.textContent = 'Chat with ' + currentChatUser; // Set the chat title with the current chat user's username

                        // Pass the loggedInUserId to the loadChatMessages function
                        loadChatMessages();

                    })
                    .catch(error => {
                        console.error('Error loading user ID:', error);
                        // Handle the error gracefully, such as displaying an error message to the user
                    });
            }
        } else {
            // If there is no saved chat user, load the messages for the first friend in the list
            if (friendList.length > 0) {
                openChat(friendList[0]);
            }
        }
    }

    // Send a message
    function sendMessage(event) {
        event.preventDefault();
        var message = messageInput.value.trim();
        if (message !== '') {
            // Make an AJAX request or use any other method to send the message to the current chat user
            // Example:
            fetch('./php/send_message.php', {
                method: 'POST',
                body: JSON.stringify({
                    recipient: currentChatUser,
                    message: message
                }),
                headers: {
                    'Content-Type': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {

                    // Message sent successfully, you can handle the response if needed

                    // Add the sent message to the chatMessages element
                    var messageElement = document.createElement('div');
                    messageElement.textContent = 'You: ' + message;
                    chatMessages.appendChild(messageElement);
                    chatMessages.scrollTop = chatMessages.scrollHeight; // Scroll to the bottom of the chat messages
                    
                })
                .catch(error => {
                    console.error('Error sending message:', error);
                    messageInput.value = '';
                    console.log(error);
                });
        }
        setTimeout(loadChatMessages, 1000);

    }

    // Call the loadInitialChat function when the page is loaded or refreshed
    window.addEventListener('load', loadInitialChat);

</script>

</body>
</html>
