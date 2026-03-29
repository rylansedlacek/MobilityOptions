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
    <?php
    require_once('database/dbReports.php');
    $previousReports = get_all_reports();
    ?>
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
                    <input type="hidden" name="action" value="generate">
                    <input type="submit" value="Generate Report" class="button generate-btn">
                </div>
            </form>

        
        </div>

        <div class="center-header">
            <h1 style="color:black;">Previous Operational Reports</h1>
        </div>

        <div class="main-content-box">
    <?php if (empty($previousReports)) { ?>
        <p>No previous reports found.</p>
    <?php } else { ?>
    <!--This is kinda ugly but it works for now x-->
        <table border="1" cellpadding="8" cellspacing="0" style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr>
                    <th>Report ID</th>
                    <th>Created At</th>
                    <th>Total Trips</th>
                    <th>Total Completed</th>
                    <th>Total Drivers</th>
                    <th>Total Vehicles</th>
                    <th>Download CSV</th>
                    <th>Download Excel</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($previousReports as $report) { ?>
                    <tr>
                        <td><?php echo (int)$report['report_id']; ?></td>
                        <td><?php echo htmlspecialchars($report['created_at']); ?></td>
                        <td><?php echo (int)$report['total_trips']; ?></td>
                        <td><?php echo (int)$report['total_completed']; ?></td>
                        <td><?php echo (int)$report['total_drivers']; ?></td>
                        <td><?php echo (int)$report['total_vehicles']; ?></td>
                        <td>
                            <form method="POST" action="processReport.php" style="margin: 0;">
                                <input type="hidden" name="action" value="download_existing">
                                <input type="hidden" name="report_id" value="<?php echo (int)$report['report_id']; ?>">
                                <input type="hidden" name="format" value="csv">
                                <input type="submit" value="CSV" class="button">
                            </form>
                        </td>
                        <td>
                            <form method="POST" action="processReport.php" style="margin: 0;">
                                <input type="hidden" name="action" value="download_existing">
                                <input type="hidden" name="report_id" value="<?php echo (int)$report['report_id']; ?>">
                                <input type="hidden" name="format" value="excel">
                                <input type="submit" value="Excel" class="button">
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } ?>
</div>

        <!-- Return Button -->
        <div style="text-align: center; margin-top: 2rem;">
            <a href="index.php" class="button" style="display: inline-block; text-decoration: none; width: 41%;">Return to Dashboard</a>
        </div>

    </main>

    
</body>
</html>

