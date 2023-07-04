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


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reto - Login</title>
    <link rel="stylesheet" href="./css/styles.css">
    <link rel="stylesheet" href="./css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <div class="login-container">
        <form method="POST" action="">
        <h2>Login</h2>
                <div class="input-holder">
                <div class="icon"><i class="fa-solid fa-user fa-lg" style="color: #ffffff;"></i></div>
                <input type="text" name="username" id="username" placeholder="Username" required>
                <div class="icon"><i class="fa-solid fa-unlock-keyhole fa-lg" style="color: #ffffff;"></i></div>
                <input type="password" name="password" id="password" placeholder="Password" required>
                </div>
                <div class="submit-holder">
                <button type="submit">Login</button>
                </div>
    <div class="error-holder">
            <?php if (isset($error)) : ?>
            <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>
    </div>

    <a href="signup">No Account Yet? Click here to Register</a>
        </form>
    </div>
</body>
</html>
