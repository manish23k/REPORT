<!--
// Database connection details
//$host = 'localhost';
//$database = 'asterisk';
//$username = 'cron';
//$password = '1234';

// Establish a database connection
//$mysqli = new mysqli($host, $username, $password, $database);

// Check for connection errors
//if ($mysqli->connect_error) {
 //   die('Connection failed: ' . $mysqli->connect_error); -->

<?php
session_start(); // Start the session on each page

include "config.php"; // Include the database configuration

// Ensure the user is logged in, or redirect them to the login page
if (!isset($_SESSION["username"])) {
    header("Location: index.php");
    exit;
}
?>

<?php include "header.php"; ?>

<?php

// Handle date range
$startDate = isset($_POST['start_date']) ? $_POST['start_date'] : '';
$endDate = isset($_POST['end_date']) ? $_POST['end_date'] : '';

// Handle user filter
$userFilter = isset($_POST['user_filter']) ? $mysqli->real_escape_string($_POST['user_filter']) : '';

// Handle phone number filter
$phoneNumberFilter = isset($_POST['phone_number_filter']) ? $mysqli->real_escape_string($_POST['phone_number_filter']) : '';

// Fetch user data for the dropdown
$userOptions = '';
$userFilter = isset($_POST['user_filter']) ? $_POST['user_filter'] : '';

$sql = "SELECT DISTINCT user FROM recording_log";
$userResult = $conn->query($sql);
if ($userResult) {
    while ($row = $userResult->fetch_assoc()) {
        $selected = ($userFilter == $row['user']) ? 'selected' : '';
        $userOptions .= "<option value='{$row['user']}' $selected>{$row['user']}</option>";
    }
}

// Define the date range for the report
if (empty($startDate) || empty($endDate)) {
    $start_date = 'YYYY-MM-DD 00:00:00';
    $end_date = 'YYYY-MM-DD 23:59:59';
} else {
    $start_date = $startDate . ' 00:00:00';
    $end_date = $endDate . ' 23:59:59';
}

// SQL query to fetch recording log data with filter
$sql = "SELECT recording_id, extension, DATE(start_time) AS date, TIME(start_time) AS time, length_in_sec, location, lead_id, user
        FROM recording_log
        WHERE start_time >= '$start_date' AND start_time <= '$end_date'";

// Add user filter
if (!empty($userFilter)) {
    $sql .= " AND user = '$userFilter'";
}

// Add phone number filter
if (!empty($phoneNumberFilter)) {
    $sql .= " AND extension = '$phoneNumberFilter'";
}

$sql .= " ORDER BY start_time";

$result = $conn->query($sql);

// Check for query execution errors
if (!$result) {
    die('Query failed: ' . $mysqli->error);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Inbound Call Log</title>
    <!-- Add Bootstrap CSS link -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
</html>
<?php
// Output the report and search form as an HTML table with Bootstrap
echo '<!DOCTYPE html>';
echo '<html>';
echo '<head>';
echo '<title>Vicidial Recording Log Report</title>';
echo '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">';
echo '</head>';
echo '<body>';
echo '<div class="container mt-5">';
echo '<h1 class="mb-4">Vicidial Recording Log Report</h1>';

// Add search form without the "Search" filter
echo '<form method="POST">';
echo '<div class="form-row">';
echo '<div class="form-group col-md-2">';
echo '<label for="start_date">Date From:</label>';
echo '<input type="date" class="form-control" name="start_date" id="start_date" value="' . $startDate . '">';
echo '</div>';
echo '<div class="form-group col-md-2">';
echo '<label for="end_date">Date To:</label>';
echo '<input type="date" class="form-control" name="end_date" id="end_date" value="' . $endDate . '">';
echo '</div>';
echo '<div class="form-group col-md-2">';
echo '<label for="user_filter">User:</label>';
echo '<select class="form-control" name="user_filter" id="user_filter">';
echo '<option value="">All</option>';
echo $userOptions;
echo '</select>';
echo '</div>';
echo '<div class="form-group col-md-3">';
echo '<label for="phone_number_filter">Phone Number:</label>';
echo '<input type="text" class="form-control" name="phone_number_filter" id="phone_number_filter" value="' . $phoneNumberFilter . '">';
echo '</div>';
echo '<div class="form-group col-md-3">';
echo '<button type="submit" class="btn btn-primary">Search</button>';
echo '</div>';
echo '</div>';
echo '</form>';

echo '<table class="table table-bordered table-responsive">';
echo '<thead class="thead-dark">';
echo '<tr>';
echo '<th>Recording ID</th>';
echo '<th>Phone Number</th>';
echo '<th>Date</th>';
echo '<th>Time</th>';
echo '<th>Length (Seconds)</th>';
echo '<th>Location (Audio)</th>';
echo '<th>Lead ID</th>';
echo '<th>User</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

// Fetch and display the results
while ($row = $result->fetch_assoc()) {
    echo '<tr>';
    echo '<td>' . $row['recording_id'] . '</td>';
    echo '<td>' . $row['extension'] . '</td>';
    echo '<td>' . $row['date'] . '</td>';
    echo '<td>' . $row['time'] . '</td>';
    echo '<td>' . $row['length_in_sec'] . '</td>';
    echo '<td><audio controls><source src="' . $row['location'] . '" type="audio/mpeg">Your browser does not support the audio element.</audio></td>';
    echo '<td>' . $row['lead_id'] . '</td>';
    echo '<td>' . $row['user'] . '</td>';
    echo '</tr>';
}

echo '</tbody>';
echo '</table>';
echo '</div>';
echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>';
echo '<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>';
echo '</body>';
echo '</html>';

// Close the database connection
$mysqli->close();
?>
<?php include "footer.php"; ?>
