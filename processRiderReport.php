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
require_once('database/dbRiderReport.php');


// fixes old complicated one
function get_post_value($key, $defaultValue)
{
    if (isset($_POST[$key])) {
        return $_POST[$key];
    }
    return $defaultValue;
}


$action = get_post_value('action', 'generate');
$format = get_post_value('format', 'csv');
$reportType = get_post_value('reportType', 'all');
$riderID = get_post_value('rider_id', null);

if ($action === 'generate') {
    if ($reportType === 'single_rider' && $riderID) {
        if ($format === 'csv') { riders_report_csv_single($riderID); }
        else {riders_report_excel_single($riderID); }
        exit();
    } 

    $snapshot = get_rider_report_information();

    if (!$snapshot) {
        echo 'Failed to generate report.';
        exit();
    }

    if ($format === 'csv') {
        riders_report_csv($snapshot);
        exit();
    }

    riders_report_excel($snapshot);
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
        riders_report_csv($snapshot);
        exit();
    }

    riders_report_excel($snapshot);
    exit();
}

// mobility options csv style report
function riders_report_csv()
{
    // $reportId = (int) $snapshot['report_id'];
    // $createdAt = $snapshot['created_at'];

    // most of this is taken from commented sections below
    header('Content-Type: text/csv');
    header("Content-Disposition: attachment; filename=rider_report.csv");
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');

    // gets the report number from where its stored in reports table
    fputcsv($output, ["Mobility Options Riders Reports"]);
    fputcsv($output, []);
    fputcsv($output, [
        'Rider name',
        'Scheduled Date',
        'Start Time',
        'End Time',
        'Mileage Start',
        'Mileage End',
        'Trip Status',
        'Pickup Location',
        'Drop Off Location',
    ]);
    foreach (get_rider_report_information() as $row) {
        $riderName = get_riders_name($row['rider_id']);
        $tripStatus = htmlspecialchars((string)$row['trip_status']);
        $tripStatusType = "";

        if ($tripStatus === "in_progress") {
            $tripStatusType = "In Progress";
        } elseif ($tripStatus === "scheduled") {
            $tripStatusType = "Scheduled";
        } elseif ($tripStatus === "completed") {
            $tripStatusType = "Completed";
        } elseif ($tripStatus === "cancelled") {
            $tripStatusType = "Cancelled";
        } else {
            $tripStatusType = "Not Scheduled";
        }

        fputcsv($output, [
            (string)$riderName,
            (string) $row['startDate'],
            (string) $row['startTime'],
            (string) $row['endTime'],
            (string) $row['mileageStart'],
            (string) $row['mileageEnd'],
            (string) $tripStatusType,
            (string) $row['pickup_location'],
            (string) $row['dropoff_location'],

        ]);
    }

    fclose($output);
} // end csv


// mobility options excel style report
function riders_report_excel()
{
    // again taken from commented out sections below
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment; filename=rider_report.xls");
    header('Pragma: no-cache');
    header('Expires: 0');

    echo "<html><head><meta charset='UTF-8'></head><body>";
    echo "<table border='1' style='border-collapse: collapse; font-family: Arial, sans-serif; text-align: center;'>";
    echo "<tr><th colspan='9' style='font-size: 18px; background-color: #004488; color: white; padding: 10px;'>Mobility Options Rider Report</th></tr>";
    echo "<tr>";
    echo "<th style='background-color: #52af3f; padding: 5px;width='100''>Rider ID</th>";
    echo "<th style='background-color: #3e87d0; padding: 5px;'>Trip Date</th>";
    echo "<th style='background-color: #52af3f; padding: 5px;'>Trip Time</th>";
    echo "<th style='background-color: #3e87d0; padding: 5px;'>End Time</th>";
    echo "<th style='background-color: #52af3f; padding: 5px;'>Mileage Start</th>";
    echo "<th style='background-color: #3e87d0; padding: 5px;'>Mileage End</th>";
    echo "<th style='background-color: #52af3f; padding: 5px;width='100''>Trip Status</th>";
    echo "<th style='background-color: #3e87d0; padding: 5px;width='100''>Pickup Location</th>";
    echo "<th style='background-color: #52af3f; padding: 5px;width='100''>Drop Off Location</th>";
    echo "</tr>";


    foreach (get_rider_report_information() as $row) {
        $riderName = get_riders_name($row['rider_id']);
        $tripStatus = htmlspecialchars((string)$row['trip_status']);
        $tripStatusType = "";

        if ($tripStatus === "in_progress") {
            $tripStatusType = "In Progress";
        } elseif ($tripStatus === "scheduled") {
            $tripStatusType = "Scheduled";
        } elseif ($tripStatus === "completed") {
            $tripStatusType = "Completed";
        } elseif ($tripStatus === "cancelled") {
            $tripStatusType = "Cancelled";
        } else {
            $tripStatusType = "Not Scheduled";
        }

        echo "<tr>";
        echo "<td style='padding: 5px;'width='100'>" . htmlspecialchars((string)$riderName) . "</td>";
        echo "<td style='padding: 5px;'>" . htmlspecialchars((string)$row['startDate']) . "</td>";
        echo "<td style='padding: 5px;'>" . htmlspecialchars((string)$row['startTime']) . "</td>";
        echo "<td style='padding: 5px;'>" . htmlspecialchars((string)$row['endTime']) . "</td>";
        echo "<td style='padding: 5px;'>" . htmlspecialchars((string)$row['mileageStart']) . "</td>";
        echo "<td style='padding: 5px;'>" . htmlspecialchars((string)$row['mileageEnd']) . "</td>";
        echo "<td style='padding: 5px;'width='100'>" . htmlspecialchars((string)$tripStatusType) . "</td>";
        echo "<td style='padding: 5px;'width='100'>" . htmlspecialchars((string)$row['pickup_location']) . "</td>";
        echo "<td style='padding: 5px;'width='100'>" . htmlspecialchars((string)$row['dropoff_location']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</body></html>";
}

function riders_report_csv_single($riderID)
{
    // $reportId = (int) $snapshot['report_id'];
    // $createdAt = $snapshot['created_at'];

    // most of this is taken from commented sections below
    header('Content-Type: text/csv');
    header("Content-Disposition: attachment; filename=rider_report.csv");
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');

    // gets the report number from where its stored in reports table
    fputcsv($output, ["Mobility Options Riders Reports"]);
    fputcsv($output, []);
    fputcsv($output, [
        'Rider name',
        'Scheduled Date',
        'Start Time',
        'End Time',
        'Mileage Start',
        'Mileage End',
        'Trip Status',
        'Pickup Location',
        'Drop Off Location',
    ]);
    foreach (get_single_rider_report_information($riderID) as $row) {
        $riderName = get_riders_name($row['rider_id']);
        $tripStatus = htmlspecialchars((string)$row['trip_status']);
        $tripStatusType = "";

        if ($tripStatus === "in_progress") {
            $tripStatusType = "In Progress";
        } elseif ($tripStatus === "scheduled") {
            $tripStatusType = "Scheduled";
        } elseif ($tripStatus === "completed") {
            $tripStatusType = "Completed";
        } elseif ($tripStatus === "cancelled") {
            $tripStatusType = "Cancelled";
        } else {
            $tripStatusType = "Not Scheduled";
        }

        fputcsv($output, [
            (string)$riderName,
            (string) $row['startDate'],
            (string) $row['startTime'],
            (string) $row['endTime'],
            (string) $row['mileageStart'],
            (string) $row['mileageEnd'],
            (string) $tripStatusType,
            (string) $row['pickup_location'],
            (string) $row['dropoff_location'],

        ]);
    }

    fclose($output);
}

function riders_report_excel_single($riderID)
{
    // again taken from commented out sections below
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment; filename=rider_report.xls");
    header('Pragma: no-cache');
    header('Expires: 0');

    echo "<html><head><meta charset='UTF-8'></head><body>";
    echo "<table border='1' style='border-collapse: collapse; font-family: Arial, sans-serif; text-align: center;'>";
    echo "<tr><th colspan='9' style='font-size: 18px; background-color: #004488; color: white; padding: 10px;'>Mobility Options Rider Report</th></tr>";
    echo "<tr>";
    echo "<th style='background-color: #52af3f; padding: 5px;width='100''>Rider ID</th>";
    echo "<th style='background-color: #3e87d0; padding: 5px;'>Trip Date</th>";
    echo "<th style='background-color: #52af3f; padding: 5px;'>Trip Time</th>";
    echo "<th style='background-color: #3e87d0; padding: 5px;'>End Time</th>";
    echo "<th style='background-color: #52af3f; padding: 5px;'>Mileage Start</th>";
    echo "<th style='background-color: #3e87d0; padding: 5px;'>Mileage End</th>";
    echo "<th style='background-color: #52af3f; padding: 5px;width='100''>Trip Status</th>";
    echo "<th style='background-color: #3e87d0; padding: 5px;width='100''>Pickup Location</th>";
    echo "<th style='background-color: #52af3f; padding: 5px;width='100''>Drop Off Location</th>";
    echo "</tr>";


    foreach (get_single_rider_report_information($riderID) as $row) {
        $riderName = get_riders_name($row['rider_id']);
        $tripStatus = htmlspecialchars((string)$row['trip_status']);
        $tripStatusType = "";

        if ($tripStatus === "in_progress") {
            $tripStatusType = "In Progress";
        } elseif ($tripStatus === "scheduled") {
            $tripStatusType = "Scheduled";
        } elseif ($tripStatus === "completed") {
            $tripStatusType = "Completed";
        } elseif ($tripStatus === "cancelled") {
            $tripStatusType = "Cancelled";
        } else {
            $tripStatusType = "Not Scheduled";
        }

        echo "<tr>";
        echo "<td style='padding: 5px;'width='100'>" . htmlspecialchars((string)$riderName) . "</td>";
        echo "<td style='padding: 5px;'>" . htmlspecialchars((string)$row['startDate']) . "</td>";
        echo "<td style='padding: 5px;'>" . htmlspecialchars((string)$row['startTime']) . "</td>";
        echo "<td style='padding: 5px;'>" . htmlspecialchars((string)$row['endTime']) . "</td>";
        echo "<td style='padding: 5px;'>" . htmlspecialchars((string)$row['mileageStart']) . "</td>";
        echo "<td style='padding: 5px;'>" . htmlspecialchars((string)$row['mileageEnd']) . "</td>";
        echo "<td style='padding: 5px;'width='100'>" . htmlspecialchars((string)$tripStatusType) . "</td>";
        echo "<td style='padding: 5px;'width='100'>" . htmlspecialchars((string)$row['pickup_location']) . "</td>";
        echo "<td style='padding: 5px;'width='100'>" . htmlspecialchars((string)$row['dropoff_location']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</body></html>";
}