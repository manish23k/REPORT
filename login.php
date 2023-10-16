
<?php
// Database connection parameters
$host = "localhost";
$username = "cron";
$password = "1234";
$database = "asterisk";

// Connect to the database
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Prepare and execute a query to fetch the user from the database
    $query = "SELECT * FROM vicidial_users WHERE user = '$username' AND pass = '$password'";
    $result = $conn->query($query);

    if ($result->num_rows == 1) {
        // Successful login
        echo "Login successful! Welcome, " . $username;
        header("Location: home.php");
    exit(); // Make sure to exit after the header redirect
        // You can redirect the user to a different page here
    } else {
        // Invalid username or password
        echo "Login failed. Please check your username and password.";
    }
}

// Close the database connection
$conn->close();
?>



