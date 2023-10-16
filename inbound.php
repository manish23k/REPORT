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
// // Database connection parameters
// $servername = "localhost";
// $username = "cron";
// $password = "1234";
// $database = "asterisk";

// // Create a database connection
// $conn = mysqli_connect($servername, $username, $password, $database);

// // Check if the connection was successful
// if (!$conn) {
//     die("Connection failed: " . mysqli_connect_error());
// }

//<?php include "header.php"; 




// Fetch Inbound Group IDs and Names from the vicidial_inbound_groups table
$inboundGroupsQuery = "SELECT group_id, group_name FROM vicidial_inbound_groups";
$inboundGroupsResult = mysqli_query($conn, $inboundGroupsQuery);

// Check if fetching inbound groups was successful
if (!$inboundGroupsResult) {
    die("Failed to fetch Inbound Groups: " . mysqli_error($conn));
}

// Fetch unique "User" values from your database
$userQuery = "SELECT DISTINCT user FROM vicidial_closer_log";
$userResult = mysqli_query($conn, $userQuery);

// Fetch unique "Status" values from your database
$statusQuery = "SELECT DISTINCT status FROM vicidial_closer_log";
$statusResult = mysqli_query($conn, $statusQuery);

// Initialize variables for campaign and date range
$campaign = isset($_GET['campaign']) ? $_GET['campaign'] : "";
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '2020-01-01';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$phoneFilter = isset($_GET['phone_number']) ? $_GET['phone_number'] : '';
$userFilter = isset($_GET['user']) ? $_GET['user'] : '';

// Check if the Search button is clicked
if (isset($_GET['search'])) {
    $campaign = $_GET['campaign'];
    $startDate = $_GET['start_date'];
    $endDate = $_GET['end_date'];
    $statusFilter = $_GET['status'];
    $phoneFilter = $_GET['phone_number'];
    $userFilter = $_GET['user'];
}

// SQL query to select specific columns and filter calls within the specified campaign and date range
$query = "SELECT list_id, campaign_id, call_date, length_in_sec, status, phone_number, user, term_reason FROM vicidial_closer_log WHERE call_date BETWEEN '$startDate' AND '$endDate'";

if (!empty($campaign)) {
    $query .= " AND campaign_id = '$campaign'";
}

if (!empty($statusFilter)) {
    $statusFilter = mysqli_real_escape_string($conn, $statusFilter);
    $query .= " AND status LIKE '%$statusFilter%'";
}

if (!empty($phoneFilter)) {
    $phoneFilter = mysqli_real_escape_string($conn, $phoneFilter);
    $query .= " AND phone_number LIKE '%$phoneFilter%'";
}

if (!empty($userFilter)) {
    $userFilter = mysqli_real_escape_string($conn, $userFilter);
    $query .= " AND user LIKE '%$userFilter%'";
}

// Execute the query
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}


//add by dhruv 

//add by dhruv 
// Check if the "Download CSV" button was clicked
if (isset($_GET['download']) && $_GET['download'] == 1) {
    // Define a filename for the CSV file
    $filename = 'inbound_call_log.csv';

    // Set the response headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    // Open a PHP output stream and set it to write to the browser
    $output = fopen('php://output', 'w');

    // Write the CSV header row
    fputcsv($output, array('List ID', 'Campaign ID', 'Date', 'Time', 'Length (sec)', 'Status', 'Phone Number', 'User', 'Term Reason'));

    // Create a new SQL query for the CSV download with applied filters
    $csvQuery = "SELECT list_id, campaign_id, call_date, length_in_sec, status, phone_number, user, term_reason FROM vicidial_closer_log WHERE call_date BETWEEN '$startDate' AND '$endDate'";

    if (!empty($campaign)) {
        $csvQuery .= " AND campaign_id = '$campaign'";
    }

    if (!empty($statusFilter)) {
        $statusFilter = mysqli_real_escape_string($conn, $statusFilter);
        $csvQuery .= " AND status LIKE '%$statusFilter%'";
    }

    if (!empty($phoneFilter)) {
        $phoneFilter = mysqli_real_escape_string($conn, $phoneFilter);
        $csvQuery .= " AND phone_number LIKE '%$phoneFilter%'";
    }

    if (!empty($userFilter)) {
        $userFilter = mysqli_real_escape_string($conn, $userFilter);
        $csvQuery .= " AND user LIKE '%$userFilter%'";
    }

    // Execute the CSV query
    $csvResult = mysqli_query($conn, $csvQuery);

    if (!$csvResult) {
        die("CSV Query failed: - Query: " . $csvQuery . " - Error: " . mysqli_error($conn));
    }

    // Loop through the query results and write each row to the CSV file
    while ($row = mysqli_fetch_assoc($csvResult)) {
        $callDate = strtotime($row['call_date']);
        $date = date("Y-m-d", $callDate);
        $time = date("H:i:s", $callDate);

        $csvRow = array(
            $row['list_id'],
            $row['campaign_id'],
            $date,
            $time,
            $row['length_in_sec'],
            $row['status'],
            $row['phone_number'],
            $row['user'],
            $row['term_reason']
        );

        fputcsv($output, $csvRow);
    }

    // Close the output stream
    fclose($output);

    // Exit to prevent rendering of the HTML page
    exit();
}
//

?>

<!DOCTYPE html>
<html>
<head>
    <title>Inbound Call Log</title>
    <!-- Add Bootstrap CSS link -->
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"> -->
</head>
<body style="background-color: #f2f2f2;"> <!-- Change background color here -->
    <div class="container mt-4">
        <h1 class="display-4">Inbound Call Log</h1>
        <form method="get" action="" class="mb-4">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="campaign">Inbound Group:</label>
                    <select name="campaign" id="campaign" class="form-select">
                        <option value="">Select an Inbound Group</option>
                        <?php
                        // Populate the dropdown with Inbound Group IDs and Names
                        while ($row = mysqli_fetch_assoc($inboundGroupsResult)) {
                            $groupID = $row['group_id'];
                            $groupName = $row['group_name'];
                            $selected = ($campaign == $groupID) ? "selected" : "";
                            echo "<option value='$groupID' $selected>$groupName</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="start_date">Start Date:</label>
                    <input type="date" name="start_date" id="start_date" value="<?= $startDate ?>" class="form-control">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="end_date">End Date:</label>
                    <input type="date" name="end_date" id="end_date" value="<?= $endDate ?>" class="form-control">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="status">Status:</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">Select Status</option>
                        <?php
                        while ($row = mysqli_fetch_assoc($statusResult)) {
                            $statusValue = $row['status'];
                            $selected = ($statusFilter == $statusValue) ? "selected" : "";
                            echo "<option value='$statusValue' $selected>$statusValue</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="phone_number">Phone Number:</label>
                    <input type="text" name="phone_number" id="phone_number" value="<?= $phoneFilter ?>" class="form-control">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="user">User:</label>
                    <select name="user" id="user" class="form-select">
                        <option value="">Select User</option>
                        <?php
                        while ($row = mysqli_fetch_assoc($userResult)) {
                            $userValue = $row['user'];
                            $selected = ($userFilter == $userValue) ? "selected" : "";
                            echo "<option value='$userValue' $selected>$userValue</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="search" class="invisible">Search Button</label>
                    <input type="submit" name="search" value="Search" class="btn btn-primary form-control">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="download_csv" class="invisible">Download CSV Button</label>
                    <a href="?download=1" class="btn btn-success form-control">Download CSV</a>
                </div>

            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>List ID</th>
                        <th>Campaign ID</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Length (sec)</th>
                        <th>Status</th>
                        <th>Phone Number</th>
                        <th>User</th>
                        <th>Term Reason</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Loop through the query results and display each row with specific columns
                    while ($row = mysqli_fetch_assoc($result)) {
                        $callDate = strtotime($row['call_date']);
                        $date = date("Y-m-d", $callDate);
                        $time = date("H:i:s", $callDate);
                        echo "<tr>";
                        echo "<td>" . $row['list_id'] . "</td>";
                        echo "<td>" . $row['campaign_id'] . "</td>";
                        echo "<td>" . $date . "</td>";
                        echo "<td>" . $time . "</td>";
                        echo "<td>" . $row['length_in_sec'] . "</td>";
                        echo "<td>" . $row['status'] . "</td>";
                        echo "<td>" . $row['phone_number'] . "</td>";
                        echo "<td>" . $row['user'] . "</td>";
                        echo "<td>" . $row['term_reason'] . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- Add Bootstrap JS and Popper.js (for Bootstrap tooltips) scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php include "footer.php"; ?>
