<?php
include_once('dbinfo.php');

function get_rider_report_information(){
    $connection = connect();
    $query = "SELECT report_id, event_id, rider_id, startDate, endDate, startTime, endTime, mileageStart, mileageEnd, trip_status, pickup_location, dropoff_location FROM rider_reports";
    $result = mysqli_query($connection, $query);

    if(!$result){
        mysqli_close($connection);
        return [];
    }

    $riderReport = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_close($connection);
    return $riderReport;
}

function get_riders_name($rider_id){
    $connection = connect();
    $query = "SELECT first_name, last_name FROM dbpersons WHERE id = '$rider_id'";
    $result = mysqli_query($connection, $query);
    
    if(!$result){
        mysqli_close($connection);
        return [];
    }

    $rider = mysqli_fetch_assoc($result);
    mysqli_close($connection);
    return $rider['first_name'] . ' ' . $rider['last_name'];
}

function get_single_rider_report_information($riderID){
    $connection = connect();
    $query = "SELECT report_id, event_id, rider_id, startDate, endDate, startTime, endTime, mileageStart, mileageEnd, trip_status, pickup_location, dropoff_location FROM rider_reports WHERE rider_id = ?";
    $stmt = mysqli_prepare($connection, $query);

    if(!$stmt){
        mysqli_close($connection);
        return [];
    }

    mysqli_stmt_bind_param($stmt, "s", $riderID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if(!$result) {
        mysqli_stmt_close($stmt);
        mysqli_close($connection);
        return[];
    }

    $riderReport = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    mysqli_close($connection);
    return $riderReport;
}