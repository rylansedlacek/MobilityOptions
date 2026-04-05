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

$accessLevel = 0;
$userID = null;
if (isset($_SESSION['_id'])) {
    $loggedIn = true;
    // 0 = not logged in, 1 = standard user, 2 = manager (Admin), 3 super admin (TBI)
    $accessLevel = $_SESSION['access_level'];
    $userID = $_SESSION['_id'];
}

include 'database/dbEvents.php';
include 'database/dbReports.php';
include 'database/dbPersons.php';
include 'database/dbRiderReport.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $eventID = (int)$_GET['id'];
    $event = fetch_event_by_id($eventID);
    if ($event && dispatch_trip($eventID)) {
        header("Location: generateRiderReports.php?status=success");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title> Mobility Options | Operational Reports</title>
    <!--<script src="js/data-filters.js" defer></script>-->
    <link href="css/normal_tw.css" rel="stylesheet">
    <?php
    $tailwind_mode = true;
    require_once('header.php');
    ?>
    <style>
        body,
        main {
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
            <h1>Generate Rider Reports</h1>
        </div>
    </header>
    <main>
        <div class="main-content-box w-[80%] p-8">
            <form method="POST" action="processRiderReport.php">
                <div style="margin-bottom: 1.5rem;">
                    <label style="font-weight: 600;">Report Contents</label>
                    <p class="sub-text" style="font-size: 16px; margin-top: 0.5rem; margin-bottom: 0.5rem;">
                        Select the format in which you would like to generate the rider report for all riders.
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
                <h1>Individual Rider Reports</h1>
            </div>
        </header>
        <!-- //////////////////////////////generate table below (format viewAllTabs.php/////////////////////////////////////// -->

        <div class="main-content-box w-[80%] p-8">
            <?php
            $events = get_all_events();
            $drivers  = get_drivers_with_email();
            $vehicles = get_vehicles();
            //$riderReports = get_rider_report_information();
            $riders = getall_persons();
            

            if (!empty($riders)): ?>
                <div class="table-wrapper">
                    <label> Select the Rider you would like to generate a report for below:<br></label>
                    <table class="general">
                        <thead>
                            <tr>
                                <!-- <th><b>Report ID</b></th> -->
                                <th><b>First Name</b></th>
                                <th><b>Last Name</b></th>
                                <th><b>Rider ID</b></th>
                                <th><b>Download Report</b></th>
                            </tr>
                        </thead>
                        <!-- <?php
                                $vehicleMap = [];
                                foreach ($vehicles as $v) {
                                    $vehicleMap[(int)$v['id']] = $v;
                                }
                                ?> -->
                        <tbody class="standout">
                            <?php foreach ($riders as $rider): ?>
                                <tr>
                                    <td><?= htmlspecialchars($rider->get_first_name()) ?></td>
                                    <td><?= htmlspecialchars($rider->get_last_name()) ?></td>
                                    <td><?= $rider->get_id() ?></td>
                                    <td>
                                        <form method="POST" action="processRiderReport.php" style="display:inline;">
                                            <input type="hidden" name="action" value="download_rider">
                                            <input type="hidden" name="rider_id" value="<?= $rider->get_id() ?>">
                                            <label for="format">Select Report Format:</label>
                                            <select name="format" class="rider-format-select">
                                                <option value="csv">CSV (.csv)</option>
                                                <option value="excel">Excel (.xls)</option>
                                            </select>
                                            <button type="submit">Download Report</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="no-events standout"> There are currently no trips available to view.<a class="button add" href="addEvent.php">Create a New Trip</a> </p>
            <?php endif ?>
        </div>
        <div class="text-center mt-6">
            <a href="index.php" class="return-button">Return to Dashboard</a>
        </div>
    </main>
</body>