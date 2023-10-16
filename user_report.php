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

<!DOCTYPE html>
<html>
<head>
    <title>Vicidial Calls Report</title>
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"> -->
</head>
<body>
    <h2>Vicidial Calls Report</h2>

    <form method="post">
        <label for="date_from">From Date:</label>
        <input type="date" name="date_from" required value="<?php echo date('Y-m-d'); ?>">

        <label for="date_to">To Date:</label>
        <input type="date" name="date_to" required value="<?php echo date('Y-m-d'); ?>">

        <label for="user">Select User:</label>
        <select name="user">
            <option value="">All Users</option>
            <?php
            // Replace with your database connection
            $db = new mysqli($hostname, $username, $password, $database);
            if ($db->connect_error) {
                die("Connection failed: " . $db->connect_error);
            }

            $user_query = "SELECT DISTINCT user FROM vicidial_users";
            $user_result = $db->query($user_query);

            if ($user_result->num_rows > 0) {
                while ($user_row = $user_result->fetch_assoc()) {
                    echo "<option value='" . $user_row['user'] . "'>" . $user_row['user'] . "</option>";
                }
            }
            $db->close();
            ?>
        </select>

        <input type="submit" name="search" value="Search">
    </form>

    <?php
    if (isset($_POST['search'])) {
        $date_from = $_POST['date_from'];
        $date_to = $_POST['date_to'];
        $user = $_POST['user'];

        // Replace with your database connection
        $db = new mysqli($hostname, $username, $password, $database);
        if ($db->connect_error) {
            die("Connection failed: " . $db->connect_error);
        }

        // Query for user login and logout details
        $login_logout_query = "SELECT event, DATE(event_date) AS event_date, TIME(event_date) AS event_time, campaign_id, user_group, session_id, server_ip, extension, computer_ip, phone_login, phone_ip FROM vicidial_user_log WHERE user = '$user' AND event_date >= '$date_from 00:00:01' AND event_date <= '$date_to 23:59:59' ORDER BY event_date";

        $result_login_logout = $db->query($login_logout_query);

        // Query for outgoing calls data
        $outgoing_calls_query = "SELECT * FROM vicidial_log WHERE DATE(call_date) BETWEEN '$date_from' AND '$date_to'";

        if (!empty($user)) {
            $outgoing_calls_query .= " AND user = '$user'";
        }

        $result_calls = $db->query($outgoing_calls_query);

        // Query for inbound call report
        $inbound_calls_query = "SELECT * FROM vicidial_closer_log WHERE DATE(call_date) BETWEEN '$date_from' AND '$date_to' AND user = '$user'";

        $result_inbound_calls = $db->query($inbound_calls_query);

        if ($result_login_logout->num_rows > 0 || $result_calls->num_rows > 0 || $result_inbound_calls->num_rows > 0) {
            echo "<h3>User Activity - $user</h3>";

            if ($result_login_logout->num_rows > 0) {
                echo "<p>Login and Logout Details:</p>";
                echo "<table border='1'>";
                echo "<tr><th>Event</th><th>Event Date</th><th>Event Time</th><th>Campaign ID</th><th>User Group</th><th>Session ID</th><th>Server IP</th><th>Extension</th><th>Computer IP</th><th>Phone Login</th><th>Phone IP</th></tr>";

                while ($login_logout_row = $result_login_logout->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $login_logout_row['event'] . "</td>";
                    echo "<td>" . $login_logout_row['event_date'] . "</td>";
                    echo "<td>" . $login_logout_row['event_time'] . "</td>";
                    echo "<td>" . $login_logout_row['campaign_id'] . "</td>";
                    echo "<td>" . $login_logout_row['user_group'] . "</td>";
                    echo "<td>" . $login_logout_row['session_id'] . "</td>";
                    echo "<td>" . $login_logout_row['server_ip'] . "</td>";
                    echo "<td>" . $login_logout_row['extension'] . "</td>";
                    echo "<td>" . $login_logout_row['computer_ip'] . "</td>";
                    echo "<td>" . $login_logout_row['phone_login'] . "</td>";
                    echo "<td>" . $login_logout_row['phone_ip'] . "</td>";
                    echo "</tr>";
                }

                echo "</table>";
            }

            if ($result_calls->num_rows > 0) {
                echo "<p>Outgoing Calls:</p>";
                echo "<table border='1'>";
                echo "<tr><th>Lead ID</th><th>List ID</th><th>Campaign ID</th><th>Call Date</th><th>Call Time</th><th>Length (sec)</th><th>Status</th><th>Phone Number</th><th>User</th><th>Comments</th><th>Term Reason</th><th>Recording</th></tr>";

                while ($row = $result_calls->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['lead_id'] . "</td>";
                    echo "<td>" . $row['list_id'] . "</td>";
                    echo "<td>" . $row['campaign_id'] . "</td>";
                    echo "<td>" . $row['call_date'] . "</td>";
                    echo "<td>" . $row['call_time'] . "</td>";
                    echo "<td>" . $row['length_in_sec'] . "</td>";
                    echo "<td>" . $row['status'] . "</td>";
                    echo "<td>" . $row['phone_number'] . "</td>";
                    echo "<td>" . $row['user'] . "</td>";
                    echo "<td>" . $row['comments'] . "</td>";
                    echo "<td>" . $row['term_reason'] . "</td>";
                    echo "<td>";
                    // Fetch recording based on lead_id, user, and vicidial_id
                    $recording_query = "SELECT location FROM recording_log WHERE lead_id = '{$row['lead_id']}' AND user = '$user' AND vicidial_id = '{$row['vicidial_id']}'";
                    $recording_result = $db->query($recording_query);
                    if ($recording_result->num_rows > 0) {
                        $recording_row = $recording_result->fetch_assoc();
                        echo "<audio controls>";
                        echo "<source src='" . $recording_row['location'] . "' type='audio/mpeg'>";
                        echo "Your browser does not support the audio element.";
                        echo "</audio>";
                        echo " <a href='" . $recording_row['location'] . "' download>Download</a>";
                    } else {
                        echo "No recording available";
                    }
                    echo "</td>";
                    echo "</tr>";
                }

                echo "</table>";
            }

            if ($result_inbound_calls->num_rows > 0) {
                echo "<p>Inbound Call Report:</p>";
                echo "<table border='1'>";
                echo "<tr><th>List ID</th><th>Campaign ID</th><th>Call Date</th><th>Call Time</th><th>Length (sec)</th><th>Status</th><th>Phone Number</th><th>User</th><th>Term Reason</th><th>Recording</th></tr>";

                while ($inbound_row = $result_inbound_calls->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $inbound_row['list_id'] . "</td>";
                    echo "<td>" . $inbound_row['campaign_id'] . "</td>";
                    echo "<td>" . $inbound_row['call_date'] . "</td>";
                    echo "<td>" . $inbound_row['call_time'] . "</td>";
                    echo "<td>" . $inbound_row['length_in_sec'] . "</td>";
                    echo "<td>" . $inbound_row['status'] . "</td>";
                    echo "<td>" . $inbound_row['phone_number'] . "</td>";
                    echo "<td>" . $inbound_row['user'] . "</td>";
                    echo "<td>" . $inbound_row['term_reason'] . "</td>";
                    echo "<td>";
                    // Fetch recording based on lead_id, user, and vicidial_id
                    $recording_query = "SELECT location FROM recording_log WHERE lead_id = '{$inbound_row['lead_id']}' AND user = '$user' AND vicidial_id = '{$inbound_row['vicidial_id']}'";
                    $recording_result = $db->query($recording_query);
                    if ($recording_result->num_rows > 0) {
                        $recording_row = $recording_result->fetch_assoc();
                        echo "<audio controls>";
                        echo "<source src='" . $recording_row['location'] . "' type='audio/mpeg'>";
                        echo "Your browser does not support the audio element.";
                        echo "</audio>";
                        echo " <a href='" . $recording_row['location'] . "' download>Download</a>";
                    } else {
                        echo "No recording available";
                    }
                    echo "</td>";
                    echo "</tr>";
                }

                echo "</table>";
            }
        } else {
            echo "<h3>User Activity - $user</h3>";
            echo "No user activity found for the selected criteria.";
        }

        $db->close();
    }
    ?>
</body>
</html>
<?php include "footer.php"; ?>
