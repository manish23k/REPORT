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
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"> -->
    <title>Vicidial Interactive Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
        }

        header {
            background-color: #333;
            color: #fff;
            text-align: center;
            padding: 10px;
        }

        h1 {
            margin-top: 10px;
            text-align: center;
        }

        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            padding: 20px;
        }

        .card {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
            padding: 20px;
            margin: 10px;
            flex: 1;
            min-width: 300px;
            text-align: center;
        }

        .red-text {
            color: red;
        }
    </style>
</head>
<body>
    <header>
        <h1>Vicidial Interactive Dashboard</h1>
        <div id="refresh-control">
            Auto Refresh:
            <label for="enableRefresh">Enable</label>
            <input type="checkbox" id="enableRefresh" checked>
            <label for="refreshInterval">Refresh Interval (seconds):</label>
            <input type="number" id="refreshInterval" value="60">
        </div>
    </header>

    <div class="container">
        <?php
        // // Replace 'your_db_credentials' with your database connection details
        // $db = new mysqli('localhost', 'cron', '1234', 'asterisk');

        // if ($db->connect_error) {
        //     die('Database connection failed: ' . $db->connect_error);
        // }

        // Define and execute each query
        $queries = [
            "Live Calls" => "SELECT COUNT(vicidial_auto_calls.`status`) FROM vicidial_auto_calls",
            "Calls in IVR OK" => "SELECT COUNT(vicidial_auto_calls.`status`) FROM vicidial_auto_calls WHERE vicidial_auto_calls.`status` = 'LIVE'",
            "Calls Waiting OK" => "SELECT COUNT(vicidial_auto_calls.`status`) FROM vicidial_auto_calls WHERE vicidial_auto_calls.`status` = 'IVR'",
            "Calls Ringing" => "SELECT COUNT(vicidial_auto_calls.call_type) FROM vicidial_auto_calls WHERE vicidial_auto_calls.stage = 'START'",
            "Agents on Call OK" => "SELECT COUNT(vicidial_live_agents.`user`) FROM vicidial_live_agents WHERE vicidial_live_agents.`status` = 'INCALL'",
            "Agents Available OK" => "SELECT COUNT(vicidial_live_agents.`user`) FROM vicidial_live_agents WHERE vicidial_live_agents.`status` in ('READY', 'CLOSER')",
            "Agents on Pause OK" => "SELECT COUNT(vicidial_live_agents.`user`) FROM vicidial_live_agents WHERE vicidial_live_agents.`status` = 'PAUSED'",
            "Inbound Total Calls" => "SELECT COUNT(vicidial_list.`status`) FROM vicidial_list WHERE vicidial_list.entry_date >= '" . date("Y-m-d") . "'",
            "Inbound Answered Calls" => "SELECT COUNT(vicidial_list.`status`) FROM vicidial_list WHERE vicidial_list.entry_date >= '" . date("Y-m-d") . "' AND vicidial_list.`status` NOT LIKE 'DROP' AND vicidial_list.`status` NOT LIKE 'TIMEOT' AND vicidial_list.`status` NOT LIKE 'INBND'",
            "Inbound Drop Calls" => "SELECT COUNT(vicidial_list.`status`) FROM vicidial_list WHERE vicidial_list.entry_date >= '" . date("Y-m-d") . "' AND (vicidial_list.`status` = 'DROP' OR vicidial_list.`status` = 'TIMEOT' OR vicidial_list.`status` = 'INBND')",
            "Outbound Total Calls OK" => "SELECT COUNT(vicidial_log.uniqueid) AS total_calls FROM vicidial_log WHERE `call_date` > '" . date("Y-m-d") . "' AND vicidial_log.`status` NOT LIKE 'CANCEL' AND vicidial_log.`status` NOT LIKE 'DOCCOM' AND vicidial_log.`status` NOT LIKE 'CALLBK' AND vicidial_log.`status` NOT LIKE 'WSD' AND vicidial_log.`status` NOT LIKE 'DCMX' AND vicidial_log.`status` NOT LIKE 'ADC'",
            "Outbound Answered Calls" => "SELECT COUNT(vicidial_log.`status`) FROM vicidial_log WHERE vicidial_log.call_date >= '" . date("Y-m-d") . "' AND `user` <> 'VDAD'",
            "Outbound Drop Calls Today" => "SELECT COUNT(vicidial_log.uniqueid) AS total_calls FROM vicidial_log WHERE `call_date` >= '" . date("Y-m-d") . "' AND `status` = 'DROP'",
        ];

        foreach ($queries as $title => $query) {
            $result = $db->query($query);
            $row = $result->fetch_row();
            $count = $row[0];
            $class = '';

            // Check if the title is "Inbound Drop Calls" or "Outbound Drop Calls Today"
            if ($title === "Inbound Drop Calls" || $title === "Outbound Drop Calls Today") {
                $class = 'red-text';
            }

            echo "<div class='card $class'><h2>$title</h2><p>$count</p></div>";
            $result->close();
        }

        $db->close();
        ?>
    </div>

    <script>
        const enableRefreshCheckbox = document.getElementById('enableRefresh');
        const refreshIntervalInput = document.getElementById('refreshInterval');
        let refreshTimer;

        function refreshData() {
            location.reload();
        }

        enableRefreshCheckbox.addEventListener('change', function () {
            if (enableRefreshCheckbox.checked) {
                const interval = parseInt(refreshIntervalInput.value, 10) * 1000;
                refreshTimer = setInterval(refreshData, interval);
            } else {
                clearInterval(refreshTimer);
            }
        });

        refreshIntervalInput.addEventListener('change', function () {
            if (enableRefreshCheckbox.checked) {
                clearInterval(refreshTimer);
                const interval = parseInt(refreshIntervalInput.value, 10) * 1000;
                refreshTimer = setInterval(refreshData, interval);
            }
        });
    </script>
</body>
</html>
<?php include "footer.php"; ?>
