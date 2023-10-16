<!DOCTYPE html>
<html>
<head>
    <title>Vicidial Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* add style here */
        
    </style>  
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="#">Vicidial Reports</a>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
            <li class="nav-item <?php if(basename($_SERVER['PHP_SELF']) == 'home.php') echo 'active'; ?>">
                <a class="nav-link" href="home.php">Home</a>
            </li>
            <li class="nav-item <?php if(basename($_SERVER['PHP_SELF']) == 'dashboard.php') echo 'active'; ?>">
                <a class="nav-link" href="dashboard.php">Dashboard</a>
            </li>
            <li class="nav-item <?php if(basename($_SERVER['PHP_SELF']) == 'inbound.php') echo 'active'; ?>">
                <a class="nav-link" href="inbound.php">Inbound Report</a>
            </li>
            <li class="nav-item <?php if(basename($_SERVER['PHP_SELF']) == 'recording.php') echo 'active'; ?>">
                <a class="nav-link" href="recording.php">Recording</a>
            </li>
            <li class="nav-item <?php if(basename($_SERVER['PHP_SELF']) == 'statistics.php') echo 'active'; ?>">
                <a class="nav-link" href="statistics.php">Statistics</a>
            </li>
            <li class="nav-item <?php if(basename($_SERVER['PHP_SELF']) == 'user_report.php') echo 'active'; ?>">
                <a class="nav-link" href="user_report.php">User Report</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">Logout</a>
            </li>
            <!-- Add more tabs for other sections as needed -->
        </ul>
    </div>
</nav>
<div class="container">
