<?php
session_start();
require_once('db_connect.php');
require_once('../PHPMailer-master/src/PHPMailer.php');
require_once('../PHPMailer-master/src/SMTP.php');
require_once('../PHPMailer-master/src/Exception.php');


class UserRegistration {
    private $name;
    private $lastname;
    private $username;
    private $email;
    private $password;
    private $gender;
    private $birthdate;

    // Constructor
    public function __construct($name, $lastname, $username, $email, $password, $gender, $birthdate) {
        $this->name = $name;
        $this->lastname = $lastname;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->gender = $gender;
        $this->birthdate = $birthdate;

    }

    public function getEmail() {
        return $this->email;
    }
    

    // Validate password length
    private function validatePassword() {
        return strlen($this->password) >= 6;
    }

    // Validate email format
    private function validateEmail() {
        return filter_var($this->email, FILTER_VALIDATE_EMAIL);
    }

    // Calculate age based on birthdate
    private function calculateAge() {
        $birthDate = new DateTime($this->birthdate);
        $currentDate = new DateTime();
        $ageInterval = $currentDate->diff($birthDate);
        return $ageInterval->y;
    }

    // Save user data to the session variables
    private function saveToSession() {
        $_SESSION['name'] = $this->name;
        $_SESSION['lastname'] = $this->lastname;
        $_SESSION['username'] = $this->username;
        $_SESSION['email'] = $this->email;
        $_SESSION['password'] = $this->password;
        $_SESSION['gender'] = $this->gender;
        $_SESSION['birthdate'] = $this->birthdate;
        $_SESSION['age'] = $this->calculateAge();
    }

// Save user data to the database and send verification email
public function saveToDatabase() {
    global $conn; // Access the database connection variable

    if (!$this->validatePassword() || !$this->validateEmail()) {
        echo "Invalid password or email.";
        return;
    }

    // Hash the password
    $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);

    // Calculate age
    $age = $this->calculateAge();

    // Insert user data into the database with hashed password
    $sql = "INSERT INTO users (name, lastname, username, email, password, gender, birthdate, age)
            VALUES ('$this->name', '$this->lastname', '$this->username', '$this->email', '$hashedPassword', '$this->gender', '$this->birthdate', $age)";

if (mysqli_query($conn, $sql));

}
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $lastname = $_POST["lastname"];
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $gender = $_POST["gender"];
    $birthdate = $_POST["birthdate"];

    // Create UserRegistration object and save data to database
    $registration = new UserRegistration($name, $lastname, $username, $email, $password, $gender, $birthdate);
    $registration->saveToDatabase();

    // Check if a profile picture is uploaded
    if (isset($_FILES['profile_picture'])) {
        $file = $_FILES['profile_picture'];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        $file_error = $file['error'];

        // Check for file upload errors
        if ($file_error === UPLOAD_ERR_OK) {
            // Generate a unique file name
            $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
            $new_file_name = uniqid('profile_', true) . '.' . $file_ext;

            // Move the uploaded file to a directory
            $upload_path = '../uploads/profile_picture/';
            move_uploaded_file($file_tmp, $upload_path . $new_file_name);

            // Insert the profile picture path into the "profile_pictures" table
            $sql = "INSERT INTO profile_pictures (image_path)
                    VALUES ('$new_file_name')";

            if (mysqli_query($conn, $sql)) {
                // Get the ID of the inserted profile picture
                $profile_picture_id = mysqli_insert_id($conn);

                // Get the ID of the user from the "users" table
                $user_id = mysqli_insert_id($conn);

                // Update the user's profile_picture_id in the "users" table
                $sql = "UPDATE users SET profile_picture_id = '$profile_picture_id'
                        WHERE id = '$user_id'";

                if (mysqli_query($conn, $sql)) {
                    // Profile picture uploaded successfully
                    echo "Profile picture uploaded successfully!";
                } else {
                    // Failed to update user's profile_picture_id
                    echo "Failed to update profile picture ID.";
                }
            } else {
                // Failed to insert profile picture path
                echo "Failed to insert profile picture path.";
            }
        } else {
            // Error in file upload
            echo "Error uploading profile picture: " . $file_error;
        }
    }
}

// After successfully registering the user and obtaining their email
$userEmail = $registration->getEmail();

// Redirect to verify.php with the email as a parameter
header("Location: verify.php?email=" . urlencode($userEmail));
exit;
?>
