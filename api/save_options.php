<?php
// Retrieve the user ID and options from the request payload
$requestPayload = json_decode(file_get_contents('php://input'), true);
$user_id = $requestPayload['user_id'];
$age_from = $requestPayload['age_from'];
$age_to = $requestPayload['age_to'];
$gender = $requestPayload['gender'];

// TODO: Perform necessary validations and sanitization on the input data

// TODO: Connect to your database and execute the query to save the options
session_start();
require_once('../php/db_connect.php');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // Handle unauthorized access
    http_response_code(401);
    exit;
}

try {
  $dsn = "mysql:host=$database_host;dbname=$database_name;charset=utf8mb4";
  $options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
  ];

  $pdo = new PDO($dsn, $username, $password, $options);

  $query = "UPDATE match_options SET age_from = :age_from, age_to = :age_to, gender = :gender WHERE user_id = :user_id";
  $statement = $pdo->prepare($query);
  $statement->bindValue(':user_id', $user_id);
  $statement->bindValue(':age_from', $age_from);
  $statement->bindValue(':age_to', $age_to);
  $statement->bindValue(':gender', $gender);
  $statement->execute();

  $response = ['success' => true];
  echo json_encode($response);
} catch (PDOException $e) {
  $response = ['success' => false, 'message' => 'Failed to save options'];
  echo json_encode($response);
  // TODO: Handle the exception appropriately (e.g., log the error, display an error message, etc.)
}
