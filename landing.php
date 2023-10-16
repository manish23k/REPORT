<?php
// Check if the user is authenticated (you can implement this using session management or other methods)
// For this example, I assume a session variable "authenticated" is set after successful login.
session_start();
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Landing Page</title>
</head>
<body>
    <h2>Welcome to the Landing Page</h2>
    <p>This is the page users see after successfully logging in.</p>
    <p>You can customize this page with your content and features.</p>

    <!-- Add your content and features here -->

    <a href="logout.php">Logout</a> <!-- Provide a logout link to log the user out -->
</body>
</html>
