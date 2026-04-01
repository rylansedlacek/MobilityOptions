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