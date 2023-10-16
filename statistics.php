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

$fromDate = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-d', strtotime('-7 days'));
$toDate = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d');
$campaignFilter = isset($_GET['campaign']) ? $_GET['campaign'] : 'All';
$chartType = isset($_GET['chart_type']) ? $_GET['chart_type'] : 'bar';
$theme = isset($_GET['theme']) ? $_GET['theme'] : 'default';

// Define CSS styles for Bootstrap themes
$themes = [
    'default' => 'bootstrap.min.css', // Default Bootstrap theme
    'dark' => 'bootstrap-dark.min.css', // Bootstrap dark theme
    'blue' => 'bootstrap-cerulean.min.css', // Bootstrap cerulean theme
];

$themeStyles = $themes[$theme];

$sqlTotalCalls = "SELECT COUNT(*) AS total_calls FROM vicidial_log WHERE call_date BETWEEN '$fromDate 00:00:00' AND '$toDate 23:59:59'";
$sqlAnsweredCalls = "SELECT COUNT(*) AS answered_calls FROM vicidial_closer_log WHERE status = 'ANSWER' AND call_date BETWEEN '$fromDate 00:00:00' AND '$toDate 23:59:59'";
$sqlAbandonedCalls = "SELECT COUNT(*) AS abandoned_calls FROM vicidial_closer_log WHERE status = 'ABANDON' AND call_date BETWEEN '$fromDate 00:00:00' AND '$toDate 23:59:59'";

if ($campaignFilter !== 'All') {
    $sqlTotalCalls .= " AND campaign_id = '$campaignFilter'";
    $sqlAnsweredCalls .= " AND campaign_id = '$campaignFilter'";
    $sqlAbandonedCalls .= " AND campaign_id = '$campaignFilter'";
}

$resultTotalCalls = $mysqli->query($sqlTotalCalls);
$resultAnsweredCalls = $mysqli->query($sqlAnsweredCalls);
$resultAbandonedCalls = $mysqli->query($sqlAbandonedCalls);

if (!$resultTotalCalls || !$resultAnsweredCalls || !$resultAbandonedCalls) {
    die("Error executing database query: " . $mysqli->error);
}

$totalCallsData = $resultTotalCalls->fetch_assoc();
$answeredCallsData = $resultAnsweredCalls->fetch_assoc();
$abandonedCallsData = $resultAbandonedCalls->fetch_assoc();

$sqlStatusBreakdown = "SELECT status, COUNT(*) AS status_count FROM vicidial_closer_log WHERE call_date BETWEEN '$fromDate 00:00:00' AND '$toDate 23:59:59'";
if ($campaignFilter !== 'All') {
    $sqlStatusBreakdown .= " AND campaign_id = '$campaignFilter'";
}

$sqlStatusBreakdown .= " GROUP BY status";

$resultStatusBreakdown = $mysqli->query($sqlStatusBreakdown);
if (!$resultStatusBreakdown) {
    die("Error executing status breakdown query: " . $mysqli->error);
}

$statusData = array();
while ($row = $resultStatusBreakdown->fetch_assoc()) {
    $statusData[$row['status']] = $row['status_count'];
}

$sqlTotalStatusCount = "SELECT status, COUNT(*) AS total_status_count FROM (SELECT status FROM vicidial_closer_log WHERE call_date BETWEEN '$fromDate 00:00:00' AND '$toDate 23:59:59' 
                        UNION ALL 
                        SELECT status FROM vicidial_log WHERE call_date BETWEEN '$fromDate 00:00:00' AND '$toDate 23:59:59') AS combined_status";

if ($campaignFilter !== 'All') {
    $sqlTotalStatusCount .= " WHERE campaign_id = '$campaignFilter'";
}

$sqlTotalStatusCount .= " GROUP BY status";

$resultTotalStatusCount = $mysqli->query($sqlTotalStatusCount);
if (!$resultTotalStatusCount) {
    die("Error executing total status count query: " . $mysqli->error);
}

$totalStatusCountData = array();
while ($row = $resultTotalStatusCount->fetch_assoc()) {
    $totalStatusCountData[$row['status']] = $row['total_status_count'];
}

$sqlCampaigns = "SELECT campaign_id, campaign_name FROM vicidial_campaigns";
$resultCampaigns = $mysqli->query($sqlCampaigns);

$campaignOptions = "<option value='All'>All</option>";
while ($row = $resultCampaigns->fetch_assoc()) {
    $campaignId = $row['campaign_id'];
    $campaignName = $row['campaign_name'];
    $selected = ($campaignFilter == $campaignId) ? "selected" : "";
    $campaignOptions .= "<option value='$campaignId' $selected>$campaignName</option>";
}

$mysqli->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Call Statistics</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/<?php echo $themeStyles; ?>">
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-3">Call Statistics</h1>

        <form method="get" class="mb-3">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="from_date">From:</label>
                    <input type="date" name="from_date" id="from_date" class="form-control" value="<?php echo $fromDate; ?>">
                </div>
                <div class="col-md-3 mb-3">
                <label for="to_date">To:</label>
                    <input type="date" name="to_date" id="to_date" class="form-control" value="<?php echo $toDate; ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="campaign">Campaign:</label>
                    <select name="campaign" id="campaign" class="form-select">
                        <?php echo $campaignOptions; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="chart_type">Chart Type:</label>
                    <select name="chart_type" id="chart_type" class="form-select">
                        <option value="bar" <?php echo ($chartType === 'bar') ? 'selected' : ''; ?>>Bar</option>
                        <option value="pie" <?php echo ($chartType === 'pie') ? 'selected' : ''; ?>>Pie</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <label for="theme">Theme:</label>
                    <select name="theme" id="theme" class="form-select">
                        <option value="default" <?php echo ($theme === 'default') ? 'selected' : ''; ?>>Default</option>
                        <option value="dark" <?php echo ($theme === 'dark') ? 'selected' : ''; ?>>Dark</option>
                        <option value="blue" <?php echo ($theme === 'blue') ? 'selected' : ''; ?>>Blue</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary mt-2">Apply Filter</button>
                </div>
            </div>
        </form>

        <h2>Call Statistics</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Call Category</th>
                    <th>Number of Calls</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total Calls</td>
                    <td><?php echo $totalCallsData['total_calls']; ?></td>
                </tr>
                <tr>
                    <td>Answered Calls</td>
                    <td><?php echo $answeredCallsData['answered_calls']; ?></td>
                </tr>
                <tr>
                    <td>Abandoned Calls</td>
                    <td><?php echo $abandonedCallsData['abandoned_calls']; ?></td>
                </tr>
            </tbody>
        </table>

        <h2>Call Status Breakdown</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Count</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($statusData as $status => $count) {
                    echo "<tr><td>$status</td><td>$count</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Add a link to view status details -->
        <!-- <a href="status_detail.php?status_id=<?php echo $statusId; ?>" target="_blank">View Status Details</a> -->

        <!-- Chart display based on the selected chart type -->
         
        <div id="chart" class="clickable-chart"></div>

<script>
    var statusCategories = <?php echo json_encode(array_keys($statusData)); ?>;
    var statusCounts = <?php echo json_encode(array_values($statusData)); ?>;
    var chartType = '<?php echo $chartType; ?>';

    // Create the appropriate chart based on the selected chart type
    if (chartType === 'bar') {
        var data = [{
            x: statusCategories,
            y: statusCounts,
            type: 'bar'
        }];
        Plotly.newPlot('chart', data);
    } else if (chartType === 'pie') {
        var data = [{
            labels: statusCategories,
            values: statusCounts,
            type: 'pie'
        }];
        Plotly.newPlot('chart', data);
    }

    // Handle click event on pie chart slices
    document.getElementById('chart').on('plotly_click', function(eventData) {
        if (eventData.points.length > 0) {
            var selectedPoint = eventData.points[0];
            var selectedStatus = selectedPoint.label;

            // Construct the dynamic URL based on the selected status
            var dynamicURL = "http://192.168.0.201/REPORT/status_detail.php?status_id=" + selectedStatus;

            // Redirect to the constructed URL
            window.location.href = dynamicURL;
        }
    });
</script>
    </div>
</body>
</html>
<?php include "footer.php"; ?>

