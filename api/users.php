<?php
// users.php (API endpoint)

require_once('../php/db_connect.php');

// Retrieve the user ID, age, and gender from the query parameters
$user_id = $_GET['user_id'];
$age = isset($_GET['age']) ? $_GET['age'] : '';
$gender = isset($_GET['gender']) ? $_GET['gender'] : '';

// Check if the user ID is provided
if (empty($user_id)) {
    $response = [
        'success' => false,
        'message' => 'User ID is required'
    ];
    echo json_encode($response);
    exit;
}

// Retrieve user data from the database
$sql = "SELECT * FROM users";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    // Fetch all user profiles from the database
    $profiles = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $profile_user_id = $row['id'];

        // Exclude the provided user ID from filtering
        if ($profile_user_id != $user_id) {
            // Check if the user has been liked or disliked
            $liked = hasAction($user_id, $profile_user_id, 'like');
            $disliked = hasAction($user_id, $profile_user_id, 'dislike');

            // Exclude users who have been liked or disliked
            if (!$liked && !$disliked) {
                $profile_age = $row['age'];
                $profile_gender = $row['gender'];

                // Check if the age filter is provided
                if (!empty($age) && !isAgeInRange($profile_age, $age)) {
                    continue; // Skip the profile if it doesn't match the age filter
                }

                // Check if the gender filter is provided
                if (!empty($gender) && $gender != 'All' && $profile_gender != $gender) {
                    continue; // Skip the profile if it doesn't match the gender filter
                }

                $profile = [
                    'id' => $profile_user_id,
                    'username' => $row['username'],
                    'gender' => $profile_gender,
                    'bio' => $row['bio'],
                    'age' => $profile_age,
                    'location' => $row['loc'],
                ];
                $profiles[] = $profile;
            }
        }
    }

    // Return the profiles as a JSON response
    $response = [
        'success' => true,
        'profiles' => $profiles
    ];
    echo json_encode($response);
} else {
    // Return an error response if no profiles are found
    $response = [
        'success' => false,
        'message' => 'No profiles found'
    ];
    echo json_encode($response);
}

/**
 * Checks if a specific action has been recorded for a user in the friend_requests table.
 *
 * @param int $sender_id
 * @param int $receiver_id
 * @param string $action
 * @return bool
 */
function hasAction($sender_id, $receiver_id, $action) {
    global $conn;

    $sql = "SELECT COUNT(*) AS count FROM friend_requests WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) AND action = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iiiss", $sender_id, $receiver_id, $receiver_id, $sender_id, $action);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    return ($row && $row['count'] > 0);
}

/**
 * Checks if the given age is within the specified range.
 *
 * @param int $age
 * @param string $ageRange
 * @return bool
 */
function isAgeInRange($age, $ageRange) {
    $ageRange = explode('-', $ageRange);
    $minAge = (int) $ageRange[0];
    $maxAge = (int) $ageRange[1];

    return ($age >= $minAge && $age <= $maxAge);
}
?>
