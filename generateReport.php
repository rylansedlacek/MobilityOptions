<?php
session_cache_expire(30);
session_start();
ini_set("display_errors", 1);
error_reporting(E_ALL);
date_default_timezone_set("America/New_York");

// Ensure admin authentication
if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] < 2) {
    header('Location: login.php');
    die();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title> Mobility Options | Operational Reports</title>
    <!--<script src="js/data-filters.js" defer></script>-->
    <link href="css/base.css" rel="stylesheet">
    <?php require_once('header.php'); ?>
</head>
<body>
    <!-- Hero Section with Title -->
        <div class="center-header">
            <h1 style="color:black;">Generate Operational Report</h1>
        </div>
                <!-- Info Section -->

    <main>
        <div class="main-content-box">
            <form method="POST" action="processReport.php">
                <div style="margin-bottom: 1.5rem;">
                    <label style="font-weight: 600;">Report Contents</label>
                    <p style="font-size: 16px; margin-top: 0.5rem; margin-bottom: 0.5rem; color: #c2c2c2ff;">
                  Includes system totals for: trips, ride requests, scheduled rides, in-progress rides, completed rides, canceled rides, active drivers, and vehicles.
                    </p>
                </div>
                <!-- pass operations_snapshot to processReport so it knows to make our report -->
                <input type="hidden" name="reportType" value="operations_snapshot">

                <!-- Format -->
                <div style="margin-bottom: 1.5rem; margin-top: 1.5rem;">
                    <label for="format" style="font-weight: 600;">File Format</label>
                    <select name="format" id="format">
                        <option value="excel">Excel (.xls)</option>
                        <option value="csv">CSV (.csv)</option>
                    </select>
                </div>

                <div style="text-align: center; margin-top: 2rem;">
                    <input type="hidden" value="<?php echo $_SESSION['_id']; ?>" name="admin" id="admin">
                    <input type="hidden" value="<?php echo date("d-M-Y H:i:s e") ?>" name="time" id="time">
                    <input type="submit" value="Generate Report" class="button generate-btn">
                </div>
            </form>

        <!-- Return Button -->
        </div>
        <div style="text-align: center; margin-top: 2rem;">
            <a href="index.php" class="button" style="display: inline-block; text-decoration: none; width: 41%;">Return to Dashboard</a>
        </div>

    </main>
</body>
</html>

