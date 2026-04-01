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
    $snapshot = create_report();

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
        'Report ID',
        'Total Trips',
        'Total Requested Trips',
        'Total Scheduled Trips',
        'Total In Progress Trips',
        'Total Completed Trips',
        'Total Canceled Trips',
        'Total Drivers',
        'Total Vehicles',
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
} // end excel





/* Old stuff
// $currentMonth = date("m");
// $currentYear = date("Y");
// $fiscalYearStart = ($currentMonth >= 10) ? $currentYear : $currentYear - 1;
// $fiscalYearEnd = $fiscalYearStart + 1;

// // Define Fiscal Year Months
// $fiscalMonths = [
//     "10" => "October $fiscalYearStart", "11" => "November $fiscalYearStart", "12" => "December $fiscalYearStart",
//     "01" => "January $fiscalYearEnd", "02" => "February $fiscalYearEnd", "03" => "March $fiscalYearEnd",
//     "04" => "April $fiscalYearEnd", "05" => "May $fiscalYearEnd", "06" => "June $fiscalYearEnd",
//     "07" => "July $fiscalYearEnd", "08" => "August $fiscalYearEnd", "09" => "September $fiscalYearEnd"
// ];

// // Define Quarters
// $quarters = [
//     "Quarter 1" => ["10", "11", "12"],
//     "Quarter 2" => ["01", "02", "03"],
//     "Quarter 3" => ["04", "05", "06"],
//     "Quarter 4" => ["07", "08", "09"]
// ];

// // Update new volunteer status before fetching report data
// update_new_volunteer_status();

// Fetch Data
$reportData = [];
$event = retrieve_event($eventID);
$eventName = $event->getName();
$num_attended = fetch_num_attendees($eventID);
$num_attended = $num_attended['RowCount'];
$capacity = $event->getCapacity();

$reportData[$eventID] = [
    // capacity, total attendance, total no shows
    "capacity" => $capacity,
    "attended" => $num_attended,
    "no_shows" => $capacity-$num_attended
];

// if ($reportType === "monthly" && isset($fiscalMonths[$month])) {
//     $monthName = $fiscalMonths[$month];
//     $dateFrom = ($month >= 10) ? "$fiscalYearStart-$month-01" : "$fiscalYearEnd-$month-01";

//     // ✅ Check if month is complete
//     if (!is_month_complete($dateFrom)) {
//         echo "<script>alert('The selected month is not yet complete. Please try again later.'); window.history.back();</script>";
//         exit();
//     }

//     $dateTo = date("Y-m-t", strtotime($dateFrom));

//     $reportData[$monthName] = [
//         "total_volunteers" => get_total_volunteers_count($dateTo),
//         "new_volunteers" => get_new_volunteers_count($dateFrom, $dateTo),
//         "new_dog_walkers" => get_new_dog_walkers_count($dateFrom, $dateTo),
//         "group_volunteers" => get_group_volunteers_count($dateFrom, $dateTo),
//         "community_service_volunteers" => get_community_service_volunteers_count($dateFrom, $dateTo),
//         "total_volunteer_hours" => get_total_vol_hours($dateFrom, $dateTo)
//     ];
// } else {
//     // Fetch for Full Fiscal Year (Annual Report)
//     foreach ($fiscalMonths as $monthNum => $monthName) {
//         $dateFrom = ($monthNum >= 10) ? "$fiscalYearStart-$monthNum-01" : "$fiscalYearEnd-$monthNum-01";
//         $dateTo = date("Y-m-t", strtotime($dateFrom));

//         $reportData[$monthName] = [
//             "total_volunteers" => get_total_volunteers_count($dateTo),
//             "new_volunteers" => get_new_volunteers_count($dateFrom, $dateTo),
//             "new_dog_walkers" => get_new_dog_walkers_count($dateFrom, $dateTo),
//             "group_volunteers" => get_group_volunteers_count($dateFrom, $dateTo),
//             "community_service_volunteers" => get_community_service_volunteers_count($dateFrom, $dateTo),
//             "total_volunteer_hours" => get_total_vol_hours($dateFrom, $dateTo)
//         ];
//     }
// }

// CSV EXPORT
if ($format === 'csv') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=attendance_report_{$eventID}_{$eventName}.csv");
    header("Pragma: no-cache");
    header("Expires: 0");

    $output = fopen('php://output', 'w');
    fputcsv($output, ["Attendance Report - Event " . $eventID . ": {$eventName}"]);

    // Column Headers
    fputcsv($output, ["ID", "Capacity", "Attended", "No Shows"]);

    // Data
    foreach ($reportData as $eventID => $data) {
        fputcsv($output, [
            $eventID,
            $data["capacity"],
            $data["attended"],
            $data["no_shows"]
        ]);
    }
    fclose($output);
    exit();
}

// EXCEL EXPORT
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=attendance_report_{$eventID}_{$eventName}.xls");
header("Pragma: no-cache");
header("Expires: 0");

echo "<html><head><meta charset='UTF-8'></head><body>";
echo "<table border='1' style='border-collapse: collapse; font-family: Arial, sans-serif; text-align: center;'>";

// Report Title
echo "<tr><th colspan='4' style='font-size: 18px; background-color: #004488; color: white; padding: 10px;'>Attendance Report - " . $eventID . ": {$eventName}</th></tr>";

// Column Headers
echo "<tr>
        <th style='background-color: #88CCEE; padding: 5px;'>Event ID</th>
        <th style='background-color: #AA4499; padding: 5px;'>Capacity</th>
        <th style='background-color: #DDCC77; padding: 5px;'>Attended</th>
        <th style='background-color: #88CCEE; padding: 5px;'>No Shows</th>
      </tr>";

// Data Rows
foreach ($reportData as $eventID => $data) {
    echo "<tr>
            <td style='background-color: #EAEAEA; padding: 5px; text-align: center;'>$eventID</td>
            <td style='padding: 5px;'>{$data["capacity"]}</td>
            <td style='padding: 5px;'>{$data["attended"]}</td>
            <td style='padding: 5px;'>{$data["no_shows"]}</td>
          </tr>";
}

echo "</table>";
echo "</body></html>";
exit();
*/
?>
