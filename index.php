<?php
session_start(); // Start the session on each page

include "config.php"; // Include the database configuration

// Check if the user is already logged in and redirect to home.php
if (isset($_SESSION["username"])) {
    header("Location: home.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // You should implement proper validation and security here
    // For simplicity, we're not hashing the password in this example.

    $query = "SELECT * FROM vicidial_users WHERE user='$username' AND pass='$password'";
    $result = $conn->query($query);

    if ($result->num_rows == 1) {
        $_SESSION["username"] = $username; // Store user information in the session
        header("Location: home.php"); // Redirect to the dashboard
        exit;
    } else {
        $error = "Invalid username or password";
    }
}
?>



<h2>Login</h2>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Custom styles for the navigation bar */
        .navbar {
            background-color: #007BFF; /* Background color */
        }

        .navbar-brand {
            color: #fff; /* Text color */
        }

        .navbar-brand:hover {
            color: #fff; /* Text color on hover */
        }

        .navbar-nav .nav-link {
            color: #333; /* Text color */
        }

        .navbar-nav .nav-link:hover {
            color: #007BFF; /* Text color on hover */
        }

        .navbar-nav .nav-item.active {
            background-color: #007BFF; /* Background color for active tab */
        }
    </style>
<form method="post">
    <div class="form-group">
        <label for="username">Username:</label>
        <input type="text" class="form-control" id="username" name="username" required>
    </div>
    <div class="form-group">
        <label for="password">Password:</label>
        <input type="password" class="form-control" id="password" name="password" required>
    </div>
    <button type="submit" class="btn btn-primary">Login</button>
</form>
<?php if (isset($error)) { echo "<div class='text-danger'>$error</div>"; } ?>

<?php include "footer.php"; ?>
