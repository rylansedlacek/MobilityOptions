<?php
include_once('dbinfo.php');

// main function that calls others
function create_report(){
    $connection = connect();
    if (!$connection) {  return null; }

    // 1 get all counts from various dbs
    $metrics = report_counts($connection);

    // 2 insert these counts into the db
    $reportId = insert_report($connection, $metrics);
    if (!$reportId) { mysqli_close($connection); return null; }

    // 3 get the newest insert report for the export
    $snapshot = recent_report($connection, $reportId);

    mysqli_close($connection);
    return $snapshot;
}

function report_counts($connection) {
    // all of our fields stored as a matrix
    $metrics = [
        'total_trips' => 0,
        'total_scheduled' => 0,
        'total_in_progress' => 0,
        'total_canceled' => 0,
        'total_completed' => 0,
        'total_requested' => 0,
        'total_drivers' => 0,
        'total_vehicles' => 0,
    ];

    // get counts from dbEvents
    $sql = "
        select
            count(*) as total_trips,
            count(case when completed = 'N' then 1 end) as total_requested,
            count(case when completed = 'Y' then 1 end) as total_scheduled,
            count(case when trip_status = 'in_progress' then 1 end) as total_in_progress,
            count(case when trip_status = 'completed' then 1 end) as total_completed,
            count(case when trip_status = 'cancelled' then 1 end) as total_canceled
        from dbevents
    ";

    $result = mysqli_query($connection, $sql);

    if ($result) {
        $row = mysqli_fetch_assoc($result);
        if ($row) {
            $metrics['total_scheduled'] = get_value_int($row, 'total_scheduled');
            $metrics['total_in_progress'] = get_value_int($row, 'total_in_progress');
            $metrics['total_canceled'] = get_value_int($row, 'total_canceled');
            $metrics['total_completed'] = get_value_int($row, 'total_completed');
            $metrics['total_requested'] = get_value_int($row, 'total_requested');
            
            $metrics['total_trips'] = $metrics['total_requested'] + $metrics['total_scheduled'] +
                $metrics['total_in_progress'] + $metrics['total_completed'] + $metrics['total_canceled'];
        }
    }

    // get driver count from dbpersons
    $driverz = "select count(*) as c from dbpersons where type = 'driver'";
    $driversResult = mysqli_query($connection, $driverz);

    if ($driversResult) {
        $driversRow = mysqli_fetch_assoc($driversResult);
        $metrics['total_drivers'] = get_value_int($driversRow, 'c'); // store
    }

    // get vehicle count from vehicles
    $vehiclez = "select count(*) as c from vehicles";
    $vehiclesResult = mysqli_query($connection, $vehiclez);

    if ($vehiclesResult) {
        $vehiclesRow = mysqli_fetch_assoc($vehiclesResult);
        $metrics['total_vehicles'] = get_value_int($vehiclesRow, 'c'); // store
    }
    return $metrics; // returns all total counts
}

// insert the counts into the reports table
function insert_report($connection, array $metrics) {
    $sql = sprintf(
        'INSERT INTO reports (total_trips, total_scheduled, total_in_progress, total_canceled, 
        total_completed, total_requested, total_drivers, total_vehicles) 
        VALUES (%d, %d, %d, %d, %d, %d, %d, %d)', // this is making me laugh dddddd
        $metrics['total_trips'],
        $metrics['total_scheduled'],
        $metrics['total_in_progress'],
        $metrics['total_canceled'],
        $metrics['total_completed'],
        $metrics['total_requested'],
        $metrics['total_drivers'],
        $metrics['total_vehicles']
    );

    $wasInserted = mysqli_query($connection, $sql);
    if (!$wasInserted) { return null;}

    $reportId = mysqli_insert_id($connection); // get report id for the fetch below
    return $reportId;
}

//get the most recent report for report generation
function recent_report($connection, $reportId) {
    $reportQuery = mysqli_prepare($connection, 'SELECT * FROM reports WHERE report_id = ? LIMIT 1' );

    if (!$reportQuery) { return null;}
    mysqli_stmt_bind_param($reportQuery, 'i', $reportId);
    mysqli_stmt_execute($reportQuery);

    $result = mysqli_stmt_get_result($reportQuery);
    $row = null;

    if ($result) { $row = mysqli_fetch_assoc($result); }

    mysqli_stmt_close($reportQuery);

    if (!$row) { return null; }
    return $row;
}

// takes values and converts them to ints cause it wont just let me cast?
function get_value_int($row, $key) {
    if (!$row) {return 0;}
    if (!array_key_exists($key, $row)) {  return 0; }
    return (int) $row[$key];
}



