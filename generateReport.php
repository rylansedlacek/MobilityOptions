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
    <!--<script src="js/data-filters.js" 0defer></script>-->
    <link href="css/normal_tw.css" rel="stylesheet">
    <?php
    $tailwind_mode = true;
    require_once('header.php');
    ?>
    <?php
    require_once('database/dbReports.php');
    $previousReports = get_all_reports();
    ?>
    <style>
        body, main {
            background-color: #fafafa;
        }

        .blue-div {
            background-color: #fafafa !important;
        }

        .main-content-box label {
            color: #000 !important;
        }

        .text-blue-700,
        .text-blue-700:visited,
        .text-blue-700:hover {
            color: #000 !important;
        }

        .sub-text {
            color: #666 !important;
        }

        select {
            width: 100%;
            max-width: 20rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            padding: 0.625rem 0.875rem;
            background-color: #fff;
        }

        .report-actions {
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <header class="hero-header">
        <div class="center-header">
            <h1>Generate Operational Report</h1>
        </div>
    </header>

    <main>
        <div class="main-content-box w-[80%] p-8">
            <form method="POST" action="processReport.php">
                <div style="margin-bottom: 1.5rem;">
                    <label style="font-weight: 600;">Report Contents</label>
                    <p class="sub-text" style="font-size: 16px; margin-top: 0.5rem; margin-bottom: 0.5rem;">
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
                    <input type="submit" value="Generate Report" class="blue-button">
                </div>
            </form>

        
        </div>

        <header class="hero-header">
            <div class="center-header">
                <h1>Previous Operational Reports</h1>
            </div>
        </header>

        <div class="main-content-box w-[80%] p-8">
            <?php
                if (count($previousReports) > 0) {
                    echo '
                    <div class="overflow-x-auto">
                        <table>
                            <thead class="bg-blue-400">
                                <tr>
                                    <th>Report ID</th>
                                    <th>Created At</th>
                                    <th>Total Trips</th>
                                    <th>Total Completed</th>
                                    <th>Total Drivers</th>
                                    <th>Total Vehicles</th>
                                    <th>Download</th>
                                </tr>
                            </thead>
                            <tbody>';

                    foreach ($previousReports as $report) {
                        echo '
                                <tr>
                                    <td>' . (int)$report['report_id'] . '</td>
                                    <td>' . (string)$report['created_at'] . '</td>
                                    <td>' . (int)$report['total_trips'] . '</td>
                                    <td>' . (int)$report['total_completed'] . '</td>
                                    <td>' . (int)$report['total_drivers'] . '</td>
                                    <td>' . (int)$report['total_vehicles'] . '</td>
                                    <td class="report-actions">
                                        <form method="POST" action="processReport.php" style="display: inline; margin: 0;">
                                            <input type="hidden" name="action" value="download_existing">
                                            <input type="hidden" name="report_id" value="' . (int)$report['report_id'] . '">
                                            <input type="hidden" name="format" value="csv">
                                            <button type="submit" class="text-blue-700 underline" style="margin-right: 1rem;">CSV</button>
                                        </form>
                                        <form method="POST" action="processReport.php" style="display: inline; margin: 0;">
                                            <input type="hidden" name="action" value="download_existing">
                                            <input type="hidden" name="report_id" value="' . (int)$report['report_id'] . '">
                                            <input type="hidden" name="format" value="excel">
                                            <button type="submit" class="text-blue-700 underline">Excel</button>
                                        </form>
                                    </td>
                                </tr>';
                    }

                    echo '
                            </tbody>
                        </table>
                    </div>';
                } else {
                    echo '<div class="error-block">No previous reports found.</div>';
                }
            ?>
        </div>

        <div class="text-center mt-6">
            <a href="index.php" class="return-button">Return to Dashboard</a>
        </div>

        <div class="info-section">
            <div class="blue-div"></div>
        </div>

    </main>

    
</body>
</html>

