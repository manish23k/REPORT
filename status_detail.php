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
// Check if a specific status or call ID is provided via GET
if (isset($_GET['status_id'])) {
    $statusId = $_GET['status_id'];

    // Query the database to fetch the status details based on the status ID
    $sqlStatusDetail = "SELECT * FROM vicidial_closer_log WHERE status = '$statusId'";
    $resultStatusDetail = $mysqli->query($sqlStatusDetail);

    if ($resultStatusDetail) {
        $statusDetail = $resultStatusDetail->fetch_assoc();
    } else {
        die("Error fetching status details: " . $mysqli->error);
    }
} else {
    // Handle the case when no status ID is provided
    die("Status ID not provided.");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Status Detail</title>
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"> -->
</head>
<body>
    <h1>Status Detail</h1>
    <table>
       <tr>
    <th>closecallid</th>
    <th>lead_id</th>
    <th>list_id</th>
    <th>campaign_id</th>
    <th>call_date</th>
    <th>start_epoch</th>
    <th>end_epoch</th>
    <th>length_in_sec</th>
    <th>status</th>
    <th>phone_code</th>
    <th>phone_number</th>
    <th>user</th>
    <th>comments</th>
    <th>processed</th>
    <th>queue_seconds</th>
    <th>user_group</th>
    <th>xfercallid</th>
    <th>term_reason</th>
    <th>uniqueid</th>
    <th>agent_only</th>
    <th>queue_position</th>
    <th>called_count</th>
</tr>      


 <tr>
    <td><?php echo $statusDetail['closecallid']; ?></td>
    <td><?php echo $statusDetail['lead_id']; ?></td>
    <td><?php echo $statusDetail['list_id']; ?></td>
    <td><?php echo $statusDetail['campaign_id']; ?></td>
    <td><?php echo $statusDetail['call_date']; ?></td>
    <td><?php echo $statusDetail['start_epoch']; ?></td>
    <td><?php echo $statusDetail['end_epoch']; ?></td>
    <td><?php echo $statusDetail['length_in_sec']; ?></td>
    <td><?php echo $statusDetail['status']; ?></td>
    <td><?php echo $statusDetail['phone_code']; ?></td>
    <td><?php echo $statusDetail['phone_number']; ?></td>
    <td><?php echo $statusDetail['user']; ?></td>
    <td><?php echo $statusDetail['comments']; ?></td>
    <td><?php echo $statusDetail['processed']; ?></td>
    <td><?php echo $statusDetail['queue_seconds']; ?></td>
    <td><?php echo $statusDetail['user_group']; ?></td>
    <td><?php echo $statusDetail['xfercallid']; ?></td>
    <td><?php echo $statusDetail['term_reason']; ?></td>
    <td><?php echo $statusDetail['uniqueid']; ?></td>
    <td><?php echo $statusDetail['agent_only']; ?></td>
    <td><?php echo $statusDetail['queue_position']; ?></td>
    <td><?php echo $statusDetail['called_count']; ?></td>
</tr>

This code will add all the columns to your HTML table for the given PHP array $statusDetail.
    </table>
</body>
</html>
<?php include "footer.php"; ?>