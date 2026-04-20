<?php

// moved schedule trip functions here for readablity

require_once(dirname(__FILE__) . '/../database/dbinfo.php');
require_once(dirname(__FILE__) . '/../database/dbEvents.php');
require_once(dirname(__FILE__) . '/../database/dbPersons.php');
require_once(dirname(__FILE__) . '/../email.php');

function format_trip_time_12h($time) {
    $dt = DateTime::createFromFormat('H:i', (string) $time);
    if ($dt instanceof DateTime) {
        return $dt->format('g:i A');
    }

    $timestamp = strtotime((string) $time);
    if ($timestamp !== false) {
        return date('g:i A', $timestamp);
    }

    return (string) $time;
}

// gabes
function driver_has_time_conflict($driver_id, $startDate, $startTime, $eventID = 0) {
    $con = connect();
    $query = "SELECT id
              FROM dbevents
              WHERE driver_id = ?
                AND startDate = ?
                AND id != ?
                AND ABS(TIME_TO_SEC(TIMEDIFF(startTime, ?))) < 1800
              LIMIT 1";
    $stmt = mysqli_prepare($con, $query);

    if (!$stmt) {
        mysqli_close($con);
        return false;
    }

    $eventID = (int) $eventID;
    mysqli_stmt_bind_param($stmt, 'ssis', $driver_id, $startDate, $eventID, $startTime);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $hasConflict = $result && mysqli_num_rows($result) > 0;
    mysqli_stmt_close($stmt);
    mysqli_close($con);

    return $hasConflict;
}

// gabes
function vehicle_has_time_conflict($vehicle_id, $startDate, $startTime, $eventID = 0) {
    $con = connect();
    $query = "SELECT id
              FROM dbevents
              WHERE vehicle_id = ?
                AND startDate = ?
                AND id != ?
                AND ABS(TIME_TO_SEC(TIMEDIFF(startTime, ?))) < 1800
              LIMIT 1";
    $stmt = mysqli_prepare($con, $query);

    if (!$stmt) {
        mysqli_close($con);
        return false;
    }

    $vehicle_id = (int) $vehicle_id;
    $eventID = (int) $eventID;
    mysqli_stmt_bind_param($stmt, 'isis', $vehicle_id, $startDate, $eventID, $startTime);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $hasConflict = $result && mysqli_num_rows($result) > 0;
    mysqli_stmt_close($stmt);
    mysqli_close($con);

    return $hasConflict;
}

// gabes
function check_duplicate_trip_request($riderID, $startDate, $startTime, $pickupLocation, $dropoffLocation, $excludeEventID = 0) {
    $con = connect();
    $query = "SELECT id
              FROM dbevents
              WHERE rider_id = ?
                AND startDate = ?
                AND startTime = ?
                AND pickup_location = ?
                AND dropoff_location = ?
                AND id != ?
              LIMIT 1";
    $stmt = mysqli_prepare($con, $query);

    if (!$stmt) {
        mysqli_close($con);
        return false;
    }

    $excludeEventID = (int) $excludeEventID;
    mysqli_stmt_bind_param(
        $stmt,
        'sssssi',
        $riderID,
        $startDate,
        $startTime,
        $pickupLocation,
        $dropoffLocation,
        $excludeEventID
    );
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $isDuplicate = $result && mysqli_num_rows($result) > 0;
    mysqli_stmt_close($stmt);
    mysqli_close($con);

    return $isDuplicate;
}

// rylan
function send_trip_scheduled_email($event, $driverID, $vehicleID) {

    $con = connect();

$stmt = mysqli_prepare($con, "SELECT Notifications FROM dbpersons WHERE id = ?");
mysqli_stmt_bind_param($stmt, "s", $event['rider_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

$riderNotif = (int)($row['Notifications'] ?? 0);

if ($riderNotif !== 1) {
    return; 
}

    if (empty($event['rider_id'])) {
        return;
    }

    $rider = retrieve_person((string) $event['rider_id']);
    if (!$rider) {
        return;
    }

    $riderEmail = trim((string) $rider->get_email());
    if (!$riderEmail || !filter_var($riderEmail, FILTER_VALIDATE_EMAIL)) {
        return;
    }

    $driverName = 'Assigned Driver';
    $driver = retrieve_person((string) $driverID);
    if ($driver) {
        $driverName = trim($driver->get_first_name() . ' ' . $driver->get_last_name());
    }

    $vehicleLabel = 'Vehicle ID: ' . (int) $vehicleID;
    foreach (get_vehicles() as $vehicle) {
        if ((int) $vehicle['id'] === (int) $vehicleID) {
            $makeModel = (string) $vehicle['make_model'];
            $plate = (string) $vehicle['plate'];
            $vehicleLabel = $makeModel . ' [ID: ' . $plate . ']';
            break;
        }
    }

    $subject = 'Trip Scheduled';
    $body = "Hello " . trim($rider->get_first_name() . ' ' . $rider->get_last_name()) . ",\n\n" .
        "Your ride request has been scheduled with the following details:\n\n" .
        "Date: {$event['startDate']}\n" .
        "Time: " . format_trip_time_12h((string) $event['startTime']) . " - " . format_trip_time_12h((string) $event['endTime']) . "\n" .
        "Pickup: {$event['pickup_location']}\n" .
        "Dropoff: {$event['dropoff_location']}\n" .
        "Driver: {$driverName}\n" .
        "Vehicle: {$vehicleLabel}\n\n" .
        "Thank you,\n" .
        "Healthy Generations - Mobility Options";

    sendEmails([$riderEmail], 'Mobility Options', $subject, $body);
}