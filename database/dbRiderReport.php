<?php
include_once('dbinfo.php');

function get_rider_report_information(){
    $connection = connect();
        $query = "SELECT
                                e.id as report_id,
                                e.id as event_id,
                                e.rider_id,
                                e.startDate,
                                e.endDate,
                                e.startTime,
                                e.endTime,
                                e.mileage_start as mileageStart,
                                e.mileage_end as mileageEnd,
                                COALESCE(NULLIF(e.trip_status, ''), case when e.completed = 'N' then 'requested' end) as trip_status,
                                e.pickup_location,
                                e.dropoff_location
                            from dbevents e
                            left join dbpersons p on p.id = e.rider_id
                            where e.rider_id is not null and e.rider_id <> ''
                            order by p.last_name asc, p.first_name asc, e.startDate desc, e.startTime desc";
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
        $query = "SELECT
                                e.id as report_id,
                                e.id as event_id,
                                e.rider_id,
                                e.startDate,
                                e.endDate,
                                e.startTime,
                                e.endTime,
                                e.mileage_start as mileageStart,
                                e.mileage_end as mileageEnd,
                                COALESCE(NULLIF(e.trip_status, ''), case when e.completed = 'N' then 'requested' end) as trip_status,
                                e.pickup_location,
                                e.dropoff_location
                            from dbevents e
                            left join dbpersons p on p.id = e.rider_id
                            where e.rider_id = ?
                            order by p.last_name asc, p.first_name asc, e.startDate desc, e.startTime desc";
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