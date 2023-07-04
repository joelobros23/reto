<?php
session_start();

// Destroy all session data
session_destroy();

// Redirect to the login page or any other appropriate page
header("Location: ../login");
exit;
?>
