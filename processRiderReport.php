<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_cache_expire(30);
session_start();

if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] < 2) {
    header('Location: login.php');
    die();
}

require_once('database/dbPersons.php');
require_once('database/dbEvents.php');
require_once('database/dbReports.php');


// fixes old complicated one
function get_post_value($key, $defaultValue) {
    if (isset($_POST[$key])) { return $_POST[$key]; }
    return $defaultValue;
}


$action = get_post_value('action', 'generate');
$format = get_post_value('format', 'csv');

if ($action === 'generate') {
    $snapshot = create_report($event_id);

    if (!$snapshot) {
        echo 'Failed to generate report.';
        exit();
    }

    if ($format === 'csv') {
        operational_report_csv($snapshot);
        exit();
    }

    operational_report_excel($snapshot);
    exit();
}

if ($action === 'download_existing') {
    $reportId = (int)get_post_value('report_id', 0);
    $snapshot = get_report_by_id($reportId);

    if (!$snapshot) {
        echo 'Report not found.';
        exit();
    }

    if ($format === 'csv') {
        operational_report_csv($snapshot);
        exit();
    }

    operational_report_excel($snapshot);
    exit();
}

// mobility options csv style report
function operational_report_csv($snapshot) {
    $reportId = (int) $snapshot['report_id'];
    $createdAt = $snapshot['created_at'];

    // most of this is taken from commented sections below
    header('Content-Type: text/csv');
    header("Content-Disposition: attachment; filename=operational_report_{$reportId}.csv");
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');

    // gets the report number from where its stored in reports table
    fputcsv($output, ["Mobility Options Operational Report #{$reportId}"]);
    fputcsv($output, ['Created At', $createdAt]);
    fputcsv($output, []);
    fputcsv($output, [
        'Rider name',
        'Start Time',
        'End Time',
        'Pickup Time',
        'Dropoff Time',
        'Mileage Start',
        'Milage End',
        'Trip Status',
    ]);

    fputcsv($output, [
        $reportId,
        (int) $snapshot['total_trips'],
        (int) $snapshot['total_requested'],
        (int) $snapshot['total_scheduled'],
        (int) $snapshot['total_in_progress'],
        (int) $snapshot['total_completed'],
        (int) $snapshot['total_canceled'],
        (int) $snapshot['total_drivers'],
        (int) $snapshot['total_vehicles'],
    ]);

    fclose($output);
} // end csv


// mobility options excel style report
function operational_report_excel($snapshot)
{
    $reportId = (int) $snapshot['report_id'];
    $createdAt = $snapshot['created_at'];


    // again taken from commented out sections below
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment; filename=operational_report_{$reportId}.xls");
    header('Pragma: no-cache');
    header('Expires: 0');

    echo "<html><head><meta charset='UTF-8'></head><body>";
    echo "<table border='1' style='border-collapse: collapse; font-family: Arial, sans-serif; text-align: center;'>";
    echo "<tr><th colspan='9' style='font-size: 18px; background-color: #004488; color: white; padding: 10px;'>Mobility Options Operational Report #{$reportId}</th></tr>";
    echo "<tr><th colspan='9' style='padding: 8px; background-color: #EAEAEA;'>Created At: {$createdAt}</th></tr>";
    echo "<tr>";
    echo "<th style='background-color: #52af3f; padding: 5px;'>Report ID</th>";
    echo "<th style='background-color: #3e87d0; padding: 5px;'>Total Trips</th>";
    echo "<th style='background-color: #52af3f; padding: 5px;'>Total Requested Trips</th>";
    echo "<th style='background-color: #3e87d0; padding: 5px;'>Total Scheduled Trips</th>";
    echo "<th style='background-color: #52af3f; padding: 5px;'>Total In Progress Trips</th>";
    echo "<th style='background-color: #3e87d0; padding: 5px;'>Total Completed Trips</th>";
    echo "<th style='background-color: #52af3f; padding: 5px;'>Total Canceled Trips</th>";
    echo "<th style='background-color: #3e87d0; padding: 5px;'>Total Drivers</th>";
    echo "<th style='background-color: #52af3f; padding: 5px;'>Total Vehicles</th>";
    echo "</tr>";
    echo "<tr>";
    echo "<td style='padding: 5px;'>" . $reportId . "</td>";
    echo "<td style='padding: 5px;'>" . (int) $snapshot['total_trips'] . "</td>";
    echo "<td style='padding: 5px;'>" . (int) $snapshot['total_requested'] . "</td>";
    echo "<td style='padding: 5px;'>" . (int) $snapshot['total_scheduled'] . "</td>";
    echo "<td style='padding: 5px;'>" . (int) $snapshot['total_in_progress'] . "</td>";
    echo "<td style='padding: 5px;'>" . (int) $snapshot['total_completed'] . "</td>";
    echo "<td style='padding: 5px;'>" . (int) $snapshot['total_canceled'] . "</td>";
    echo "<td style='padding: 5px;'>" . (int) $snapshot['total_drivers'] . "</td>";
    echo "<td style='padding: 5px;'>" . (int) $snapshot['total_vehicles'] . "</td>";
    echo "</tr>";
    echo "</table>";
    echo "</body></html>";
}