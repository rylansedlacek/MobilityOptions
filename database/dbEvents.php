<?php
/*
 * Copyright 2013 by Jerrick Hoang, Ivy Xing, Sam Roberts, James Cook, 
 * Johnny Coster, Judy Yang, Jackson Moniaga, Oliver Radwan, 
 * Maxwell Palmer, Nolan McNair, Taylor Talmage, and Allen Tucker. 
 * This program is part of RMH Homebase, which is free software.  It comes with 
 * absolutely no warranty. You can redistribute and/or modify it under the terms 
 * of the GNU General Public License as published by the Free Software Foundation
 * (see <http://www.gnu.org/licenses/ for more information).
 * 
 */

/**
 * @version March 1, 2012
 * @author Oliver Radwan and Allen Tucker
 */

/* 
 * Created for Gwyneth's Gift in 2022 using original Homebase code as a guide
 */


include_once('dbinfo.php');
include_once(dirname(__FILE__) . '/../domain/Event.php');
//Added to send emails to users when they are removed or signed up to an event.
include_once(dirname(__FILE__) . '/../email.php');

/*
 * add an event to dbEvents table: if already there, return false
 */

// NOT USED - RS
function add_event($event)
{
    // if (!$event instanceof Event)
    //     die("Error: add_event type mismatch");
    $con = connect();
    $query = "SELECT * FROM dbevents WHERE id = '" . $event->getID() . "'";
    $result = mysqli_query($con, $query);
    //if there's no entry for this id, add it
    if ($result == null || mysqli_num_rows($result) == 0) {
        mysqli_query($con, 'INSERT INTO dbevents VALUES("' .
            $event->getID() . '","' .
            $event->getName() . '","' .
            $event->getType() . '","' .
            $event->getStartDate() . '","' .
            $event->getStartTime() . "," .
            $event->getEndTime() . "," .
            $event->getEndDate() . "," .
            $event->getDescription() . '","' .
            $event->getCapacity() . "," .
            $event->getLocation() . "," .
            $event->getAttendance() . "," .
            $event->getDropoffContact() . '","' .
            $event->Access() . '","' .
            $event->getCompleted() . "," .
            #$event->getID() .            
            '");');
        mysqli_close($con);
        return true;
    }
    mysqli_close($con);
    return false;
}

/*function fetch_event_name_by_id($id) {
    $connection = connect();
    $id = mysqli_real_escape_string($connection, $id);
    $query = "select name from dbevents where id = '$id'";
    $result = mysqli_query($connection, $query);
    $event = mysqli_fetch_assoc($result);
    if ($event) {
        require_once('include/output.php');
        $event = hsc($event);
        mysqli_close($connection);
        return $event;
    }
    mysqli_close($connection);
    return null;
}*/

function request_event_signup($event_name_str, $account_name, $role, $notes)
{
    // This function is deprecated. Use create_app() in dbApplications.php for Retreat signups.
    // Kept for backwards compatibility only.
    $connection = connect();

    $safe_name = mysqli_real_escape_string($connection, $event_name_str);
    $query1 = "SELECT id FROM dbevents WHERE name = '$safe_name'";
    $result1 = mysqli_query($connection, $query1);

    if (!$result1 || mysqli_num_rows($result1) === 0) {
        mysqli_close($connection);
        return null;
    }

    $row = mysqli_fetch_assoc($result1);
    $eventID = $row['id'];
    mysqli_close($connection);
    return $eventID;
}
function sign_up_for_event($eventID, $account_name, $role, $notes)
{
    $connection = connect();

    // 1. ESCAPE INPUTS (Crucial for names like "Gwyneth's Gift")
    // This prevents the SQL query from breaking on apostrophes
    $safe_name = mysqli_real_escape_string($connection, $eventID);
    $safe_account = mysqli_real_escape_string($connection, $account_name);
    $safe_role = mysqli_real_escape_string($connection, $role);
    $safe_notes = mysqli_real_escape_string($connection, $notes);

    // 2. FETCH EVENT ID
    // We use the 'safe_name' here.
    $query1 = "SELECT id FROM dbevents WHERE name = '$safe_name'";
    $result1 = mysqli_query($connection, $query1);

    // 3. CHECK IF EVENT EXISTS
    // This check prevents the "Undefined variable" error by stopping if no event is found.
    if (!$result1 || mysqli_num_rows($result1) === 0) {
        mysqli_close($connection);
        return null; // Return failure safely
    }

    $row = mysqli_fetch_assoc($result1);
    $value = $row['id']; // Now it is safe to get the ID

    // 4. CHECK FOR DUPLICATE SIGNUP
    $query2 = "SELECT userID FROM dbeventpersons WHERE eventID = '$value' AND userID = '$safe_account'";
    $result2 = mysqli_query($connection, $query2);
    $row2 = mysqli_fetch_assoc($result2);

    if ($row2) {
        // User already signed up
        mysqli_close($connection);
        return null;
    } else {
        // 5. INSERT SIGNUP
        $query = "INSERT INTO dbeventpersons (eventID, userID, notes) VALUES ('$value', '$safe_account', '$safe_notes')";
        $result = mysqli_query($connection, $query);
        mysqli_commit($connection);
        mysqli_close($connection);
        return $value;
    }
}

/* @@@ Thomas's work! */
/*
 * Check if a user is is signed up for an event. Return true or false.
 */
function check_if_signed_up($eventID, $userID)
{
    // look up event+user pair
    $connection = connect();
    $query1 = "SELECT * FROM dbeventpersons WHERE eventID = '$eventID' and userID = '$userID'";
    $result1 = mysqli_query($connection, $query1);
    $row = mysqli_fetch_assoc($result1);
    mysqli_close($connection);

    // check if a row was returned
    if ($row) {
        return True;
    } else {
        return False;
    }
}


/* @@@ Madison's work! */
/*
 * Check for all users signed up for an event. 
 */
function fetch_event_signups($eventID)
{
    $connection = connect();
    $query = "SELECT userID, notes FROM dbeventpersons WHERE eventID = '$eventID'";
    $result = mysqli_query($connection, $query);

    if (!$result) {
        die('Query failed: ' . mysqli_error($connection));
    }

    $signups = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $signups[] = $row;
    }

    mysqli_close($connection);
    return $signups;
}

/*
 * Fetch pending signups for an event (rows in `dbpendingsignups`)
 * Returns array of rows with keys: username, role, notes
 */
function fetch_pending($eventID)
{
    $connection = connect();
    $query = "SELECT username, role, notes FROM dbpendingsignups WHERE eventname = '$eventID'";
    $result = mysqli_query($connection, $query);

    if (!$result) {
        die('Query failed: ' . mysqli_error($connection));
    }

    $signups = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $signups[] = $row;
    }

    mysqli_close($connection);
    return $signups;
}



function remove_user_from_event($event_id, $user_id)
{
    $query = "DELETE FROM dbeventpersons WHERE eventID LIKE '$event_id' AND userID LIKE '$user_id'";
    $connection = connect();
    $result = mysqli_query($connection, $query);
    $result = boolval($result);
    mysqli_close($connection);
    //If true email user 
    if ($result == TRUE) {
        emailHandler($event_id, $user_id, 1, "Removed from event because TEST");
    }
    return $result;
}



/* @@@ Thomas's work! */
/*
 * Returns true if the given event is archived.
 */
function is_archived($id)
{
    // look-up 'completed' in the event's DB entry
    $connection = connect();
    $query1 = "SELECT completed FROM dbevents WHERE id = '$id'";
    $result1 = mysqli_query($connection, $query1);
    $row = mysqli_fetch_assoc($result1);
    mysqli_close($connection);

    if ($row == NULL) return False; // no match for that event ID

    if ($row['completed'] == 'yes') {
        // event is archived
        return True;
    } else {
        return False;
    }
}

/*
 * Mark an event as archived in the DB by setting the 'completed' column to 'yes'.
 */
function archive_event($id)
{
    $con = connect();
    $query = "UPDATE dbevents SET completed = 'yes' WHERE id = '" . $id . "'";
    $result = mysqli_query($con, $query);
    mysqli_close($con);
    return $result;
}

/*
 * Mark an event as not archived in the DB by setting the 'completed' column to 'no'.
 */
function unarchive_event($id)
{
    $con = connect();
    $query = "UPDATE dbevents SET completed = 'no' WHERE id = '" . $id . "'";
    $result = mysqli_query($con, $query);
    mysqli_close($con);
    return $result;
}

/* end of Thomas's work*/

/**/

/*
 * remove an event from dbEvents table.  If already there, return false
 */

function remove_event($id)
{
    $con = connect();
    $query = 'SELECT * FROM dbevents WHERE id = "' . $id . '"';
    $result = mysqli_query($con, $query);
    if ($result == null || mysqli_num_rows($result) == 0) {
        mysqli_close($con);
        return false;
    }
    $query = 'DELETE FROM dbevents WHERE id = "' . $id . '"';
    $result = mysqli_query($con, $query);


    /* WIP writing code to remove event registrations for events that are cancelled. 
    (Cleans up database from un-needed entries)

    $query = 'SELECT * FROM dbeventpersons WHERE id = "' . $id . '"';
    $result = mysqli_query($con,$query);

    if ($result != null || mysqli_num_rows($result) != 0){
        $query = 'DELETE FROM dbeventpersons WHERE id = "' . $id . '"';
        $result = mysqli_query($con,$query);
    }
    
    */

    mysqli_close($con);
    return true;
}


/*
 * @return an Event from dbEvents table matching a particular id.
 * if not in table, return false
 */

function retrieve_event($id)
{
    $con = connect();
    $query = "SELECT * FROM dbevents WHERE id = '" . $id . "'";
    $result = mysqli_query($con, $query);
    if (mysqli_num_rows($result) !== 1) {
        mysqli_close($con);
        return false;
    }
    $result_row = mysqli_fetch_assoc($result);
    // var_dump($result_row);
    $theEvent = make_an_event($result_row);
    //    mysqli_close($con);
    return $theEvent;
}

function retrieve_event2($id)
{
    $con = connect();
    $query = "SELECT * FROM dbevents WHERE id = '" . $id . "'";
    $result = mysqli_query($con, $query);
    if (mysqli_num_rows($result) !== 1) {
        mysqli_close($con);
        return false;
    }
    $result_row = mysqli_fetch_assoc($result);
    //    var_dump($result_row);
    return $result_row;
}

// not in use, may be useful for future iterations in changing how events are edited (i.e. change the remove and create new event process)
function update_event_date($id, $new_event_date)
{
    $con = connect();
    $query = 'UPDATE dbevents SET event_date = "' . $new_event_date . '" WHERE id = "' . $id . '"';
    $result = mysqli_query($con, $query);
    mysqli_close($con);
    return $result;
}

function make_an_event($result_row)
{
    /*
	 ($en, $v, $sd, $description, $ev))
	 */
    $theEvent = new Event(
        $result_row['id'],
        $result_row['name'],
        type: $result_row['type'],
        startDate: $result_row['startDate'],
        startTime: $result_row['startTime'],
        endTime: $result_row['endTime'],
        endDate: $result_row['endDate'],
        description: $result_row['description'],
        capacity: $result_row['capacity'],
        location: $result_row['location'],
        attended: $result_row['attended'],
        dropoff_contact: $result_row['dropoff_contact'],
        access: $result_row['access'],
        completed: $result_row['completed'],
        rider_id: $result_row['rider_id'],
        driver_id: $result_row['driver_id'],
        vehicle_id: $result_row['vehicle_id'],
        pickup_location: $result_row['pickup_location'],
        dropoff_location: $result_row['dropoff_location'],
        trip_status: $result_row['trip_status'],
        mileage_start: $result_row['mileage_start'],
        mileage_end: $result_row['mileage_end'],
    );
    return $theEvent;
}

function get_all_events() {
    $con = connect();
    $query = "SELECT * FROM dbevents WHERE trip_status != 'in_progress' and trip_status != 'cancelled'" .
        " ORDER BY completed";
    $result = mysqli_query($con, $query);
    $theEvents = array();
    while ($result_row = mysqli_fetch_assoc($result)) {
        $theEvent = make_an_event($result_row);
        $theEvents[] = $theEvent;
    }
    mysqli_close($con);
    return $theEvents;
}

function get_all_prog_events() {
    $con = connect();
    $query = "SELECT * FROM dbevents WHERE trip_status = 'in_progress' and trip_status != 'cancelled'";
   
    $result = mysqli_query($con, $query);
    $theEvents = array();
    while ($result_row = mysqli_fetch_assoc($result)) {
        $theEvent = make_an_event($result_row);
        $theEvents[] = $theEvent;
    }
    mysqli_close($con);
    return $theEvents;
}

function get_pending_ride_requests() {
    $con = connect();
    $query = "select * from dbevents where completed = 'N'" .
        "and (trip_status is null or trip_status not in ('in_progress', 'cancelled', 'completed')) " .
        "order by startDate asc, startTime asc";
    $result = mysqli_query($con, $query);
    $theEvents = array();
    while ($result_row = mysqli_fetch_assoc($result)) {
        $theEvent = make_an_event($result_row);
        $theEvents[] = $theEvent;
    }
    mysqli_close($con);
    return $theEvents;
}

// Get all trips that are pending in the system - RS
// By default marked as not COMPLETED - N.
function get_pending_trips()
{
    $con = connect();
    $query = "SELECT * FROM dbevents WHERE completed = 'N' ORDER BY startDate ASC";
    $result = mysqli_query($con, $query);
    $theEvents = array();
    while ($result_row = mysqli_fetch_assoc($result)) {
        $theEvent = make_an_event($result_row);
        $theEvents[] = $theEvent;
    }
    mysqli_close($con);
    return $theEvents;
}

// same as get_pending_trips() - for backward compat
function get_all_events_sorted_by_date_not_archived()
{
    return get_pending_trips();
}

function get_all_events_sorted_by_date_and_archived()
{
    $con = connect();
    $query = "SELECT * FROM dbevents" .
        " WHERE completed = 'Y'" .
        " ORDER BY startDate ASC";
    $result = mysqli_query($con, $query);
    $theEvents = array();
    while ($result_row = mysqli_fetch_assoc($result)) {
        $theEvent = make_an_event($result_row);
        $theEvents[] = $theEvent;
    }
    mysqli_close($con);
    return $theEvents;
}

// retrieve only those events that match the criteria given in the arguments
function getonlythose_dbEvents($name, $day, $venue)
{
    $con = connect();
    $query = "SELECT * FROM dbevents WHERE event_name LIKE '%" . $name . "%'" .
        " AND event_name LIKE '%" . $name . "%'" .
        " AND venue = '" . $venue . "'" .
        " ORDER BY event_name";
    $result = mysqli_query($con, $query);
    $theEvents = array();
    while ($result_row = mysqli_fetch_assoc($result)) {
        $theEvent = make_an_event($result_row);
        $theEvents[] = $theEvent;
    }
    mysqli_close($con);
    return $theEvents;
}

function fetch_events_in_date_range($start_date, $end_date)
{
    $connection = connect();
    $start_date = mysqli_real_escape_string($connection, $start_date);
    $end_date = mysqli_real_escape_string($connection, $end_date);
    $query = "select * from dbevents
              where DATE(startDate) >= '$start_date' and DATE(startDate) <= '$end_date'
              order by DATE(startDate) asc, startTime asc";
    $result = mysqli_query($connection, $query);
    if (!$result) {
        mysqli_close($connection);
        return null;
    }
    require_once('include/output.php');
    $events = array();
    while ($result_row = mysqli_fetch_assoc($result)) {
        // Normalize to YYYY-MM-DD so both DATE and DATETIME values map correctly.
        $startDateRaw = (string)($result_row['startDate'] ?? '');
        $timestamp = strtotime($startDateRaw);
        if ($timestamp === false) {
            continue;
        }
        $key = date('Y-m-d', $timestamp);
        if (isset($events[$key])) {
            $events[$key][] = hsc($result_row);
        } else {
            $events[$key] = array(hsc($result_row));
        }
    }
    mysqli_close($connection);
    return $events;
}

function fetch_events_on_date($startDate, $loggedIn)
{
    echo "<script> console.log('fetch_events_on_date IN:', '\" . $startDate . \"');</script>";
    $connection = connect();
    $date = mysqli_real_escape_string($connection, $startDate);
    if ($loggedIn) {
        $query = "select * from dbevents
              where startDate = '$startDate' order by startTime asc";
    } else {
        $query = "select * from dbevents
              where startDate = '$startDate'
              and access = 'Public'
              order by startTime asc";
    }

    $results = mysqli_query($connection, $query);
    if (!$results) {
        mysqli_close($connection);
        return null;
    }
    require_once('include/output.php');
    $events = [];
    foreach ($results as $row) {
        $events[] = hsc($row);
    }
    mysqli_close($connection);
    return $events;
}

function fetch_event_by_id($id)
{
    $connection = connect();
    $id = mysqli_real_escape_string($connection, $id);
    $query = "select * from dbevents where id = '$id'";
    $result = mysqli_query($connection, $query);
    $event = mysqli_fetch_assoc($result);
    if ($event) {
        require_once('include/output.php');
        $event = hsc($event);
        mysqli_close($connection);
        return $event;
    }
    mysqli_close($connection);
    return null;
}
// JUST ADDED
function fetch_num_signups($id)
{
    $connection = connect();
    $id = mysqli_real_escape_string($connection, $id);
    $query = "select count(*) as RowCount from dbeventpersons where eventID = '$id'";
    $result = mysqli_query($connection, $query);
    $event = mysqli_fetch_assoc($result);
    if ($event) {
        require_once('include/output.php');
        $event = hsc($event);
        mysqli_close($connection);
        return $event;
    }
    mysqli_close($connection);
    return null;
}

function fetch_num_attendees($id)
{
    $connection = connect();
    $id = mysqli_real_escape_string($connection, $id);
    $query = "select count(*) as RowCount from dbeventpersons where eventID = '$id' and attended=1";
    $result = mysqli_query($connection, $query);
    $event = mysqli_fetch_assoc($result);
    if ($event) {
        require_once('include/output.php');
        $event = hsc($event);
        mysqli_close($connection);
        return $event;
    }
    mysqli_close($connection);
    return null;
}

function create_event($event)
{
    $connection = connect();
    $name = mysqli_real_escape_string($connection, $event["name"]);
    //$abbrevName = $event["abbrev-name"];
    // $date = $event["date"];
    $date    = $event["startDate"] ?? $event["date"];
    $endDate = $event["endDate"]   ?? $date; // default single-day
    $startTime = $event["start-time"];
    $endTime = $event["end-time"];
    $description = $event["description"];
    $type = $event['type'];
    // if (isset($event["capacity"])) {
    //     $capacity = $event["capacity"];
    // } else {
    $capacity = 999;
    // }
    if (isset($event["location"])) {
        $location = $event["location"];
    } else {
        $location = "";
    }
    $access = 'Public';
    $description = $event["description"];
    //$branch = $event["branch"];
    //$location = $event["location"];
    //$services = $event["service"];

    //$animal = $event["animal"];
    $completed = 'N';

    $series_id = isset($event['series_id']) ? $event['series_id'] : null;

    // new dbevents fields to use
    $rider_id        = $event['rider_id'] ?? null;
    $driver_id       = $event['driver_id'] ?? null;
    $vehicle_id      = $event['vehicle_id'] ?? null;
    $pickup_location = $event['pickup_location'] ?? null;
    $dropoff_location = $event['dropoff_location'] ?? null;
    $dropoff_contact = $event['dropoff_contact'] ?? null;
    $trip_status     = $event['trip_status'] ?? null;
    $mileage_start   = $event['mileage_start'] ?? null;
    $mileage_end     = $event['mileage_end'] ?? null;


    // formatting follows series Id - RS
    // $query = "
    //     insert into dbevents (
    //         name, startDate, startTime, endTime, endDate, access,
    //         description, completed, location, type, series_id,
    //         rider_id, driver_id, vehicle_id, pickup_location, dropoff_location,
    //         trip_status, mileage_start, mileage_end
    //     )
    //     values (
    //         '$name', '$date', '$startTime', '$endTime', '$endDate', '$access',
    //         '$description', '$completed', '$location', '$type', 
    //         " .($series_id ? "'$series_id'" : "NULL") . ",
    //         " .($rider_id ? "'$rider_id'" : "NULL") . ",
    //         " .($driver_id !== null ? $driver_id : "NULL") . ",
    //         " .($vehicle_id !== null ? $vehicle_id : "NULL") . ",
    //         " .($pickup_location ? "'$pickup_location'" : "NULL") . ",
    //         " .($dropoff_location ? "'$dropoff_location'" : "NULL") . ",
    //         " .($trip_status ? "'$trip_status'" : "NULL") . ",
    //         " .($mileage_start !== null ? $mileage_start : "NULL") . ",
    //         " .($mileage_end !== null ? $mileage_end : "NULL") . "
    //     )
    // ";
    $query = "
        insert into dbevents (
            name, startDate, startTime, endTime, endDate, access,
            description, capacity, completed, location, type, series_id,
            rider_id, driver_id, vehicle_id, pickup_location, dropoff_location, dropoff_contact,
            trip_status, mileage_start, mileage_end
        )
        values (
            '$name', '$date', '$startTime', '$endTime', '$endDate', '$access',
            '$description', $capacity, '$completed', '$location', '$type', 
            " . ($series_id ? "'$series_id'" : "NULL") . ",
            " . ($rider_id ? "'$rider_id'" : "NULL") . ",
            " . ($driver_id !== null ? $driver_id : "NULL") . ",
            " . ($vehicle_id !== null ? $vehicle_id : "NULL") . ",
            " . ($pickup_location ? "'$pickup_location'" : "NULL") . ",
            " . ($dropoff_location ? "'$dropoff_location'" : "NULL") . ",
            " . ($dropoff_contact ? "'$dropoff_contact'" : "NULL") . ",
            " . ($trip_status ? "'$trip_status'" : "NULL") . ",
            " . ($mileage_start !== null ? $mileage_start : "NULL") . ",
            " . ($mileage_end !== null ? $mileage_end : "NULL") . "
        )
    ";
    $result = mysqli_query($connection, $query);
    if (!$result) {
        return null;
    }
    $id = mysqli_insert_id($connection);
    //add_services_to_event($id, $services);
    mysqli_commit($connection);
    mysqli_close($connection);
    return $id;
}

function add_services_to_event($eventID, $serviceIDs)
{
    $connection = connect();
    foreach ($serviceIDs as $serviceID) {
        $query = "insert into dbeventsservices (eventID, serviceID) values ('$eventID', '$serviceID')";
        $result = mysqli_query($connection, $query);
        if (!$result) {
            return null;
        }
        $id = mysqli_insert_id($connection);
    }
    mysqli_commit($connection);
    return $id;
}

function update_event($eventID, $eventDetails)
{
    $connection = connect();
    $id = $eventDetails["id"];
    $name = $eventDetails["name"];
    #$abbrevName = $eventDetails["abbrev-name"];
    $date = $eventDetails["date"];
    $startTime = $eventDetails["start-time"];
    #$restricted = $eventDetails["restricted"];
    $endTime = $eventDetails["end-time"];
    $description = $eventDetails["description"];
    //$capacity = $eventDetails["capacity"];
    #$completed = $eventDetails["completed"];
    #$restricted_signup = $eventDetails["restricted_signup"];
    $location = $eventDetails["location"];
    //$services = $eventDetails["service"];

    #$completed = $eventDetails["completed"];
    #$query = "
    # update dbEvents set name='$name', abbrevName='$abbrevName', date='$date', startTime='$startTime', restricted='$restricted', description='$description', locationID='$location', completed='$completed'
    # where id='$eventID'
    #";
    # $query = "
    #    update dbevents set id='$id', name='$name', date='$date', startTime='$startTime', endTime='$endTime', description='$description', capacity='$capacity', completed='$completed', event_type='$event_type', restricted_signup='$restricted_signup'
    #    where id='$eventID'
    #";

    // new dbevents fields to use
    $rider_id        = $eventDetails['rider_id'] ?? null;
    $driver_id       = $eventDetails['driver_id'] ?? null;
    $vehicle_id      = $eventDetails['vehicle_id'] ?? null;
    $pickup_location = $eventDetails['pickup_location'] ?? null;
    $dropoff_location = $eventDetails['dropoff_location'] ?? null;
    $dropoff_contact = $eventDetails['dropoff_contact'] ?? null;
    $trip_status     = $eventDetails['trip_status'] ?? null;
    $mileage_start   = $eventDetails['mileage_start'] ?? null;
    $mileage_end     = $eventDetails['mileage_end'] ?? null;

    // follow same syntax as above - RS
    $query = "
        update dbevents set id='$id', name='$name', startDate='$date', endDate='$date', startTime='$startTime', endTime='$endTime', description='$description', location='$location'"
        . ($rider_id ? ", rider_id='$rider_id'" : "")
        . ($driver_id !== null ? ", driver_id=$driver_id" : "")
        . ($vehicle_id !== null ? ", vehicle_id=$vehicle_id" : "")
        . ($pickup_location ? ", pickup_location='$pickup_location'" : "")
        . ($dropoff_location ? ", dropoff_location='$dropoff_location'" : "")
        . ($dropoff_contact ? ", dropoff_contact='$dropoff_contact'" : "")
        . ($trip_status ? ", trip_status='$trip_status'" : "")
        . ($mileage_start !== null ? ", mileage_start=$mileage_start" : "")
        . ($mileage_end !== null ? ", mileage_end=$mileage_end" : "") . "
        where id='$eventID'
    ";
    $result = mysqli_query($connection, $query);
    // update_services_for_event($eventID, $services);
    mysqli_commit($connection);
    mysqli_close($connection);
    return $result;
}

function update_event2($eventID, $eventDetails)
{
    $connection = connect();
    $id = $eventDetails["id"];
    $name = $eventDetails["name"];
    #$abbrevName = $eventDetails["abbrevName"];
    $date = $eventDetails["date"];
    $startTime = $eventDetails["startTime"];
    $endTime = $eventDetails["endTime"];
    $description = $eventDetails["description"];
    $capacity = $eventDetails["capacity"];
    $completed = $eventDetails["completed"];
    $restricted_signup = $eventDetails["restricted_signup"];
    #$query = "
    #    update dbEvents set name='$name', abbrevName='$abbrevName', date='$date', startTime='$startTime', endTime='$endTime', description='$description', locationID='$location', capacity='$capacity', animalId='$animalID', completed='$completed'
    #    where id='$eventID'
    #";
    $query = "
        update dbevents set id='$id', name='$name', date='$date', startTime='$startTime', endTime='$endTime', description='$description', capacity='$capacity', completed='$completed', restricted_signup='$restricted_signup'
        where id='$eventID'
    ";
    $result = mysqli_query($connection, $query);
    //update_services_for_event($eventID, $services);
    mysqli_commit($connection);
    mysqli_close($connection);
    return $result;
}

function update_services_for_event($eventID, $serviceIDs)
{
    $connection = connect();

    $current_services = get_services($eventID);
    foreach ($current_services as $curr_serv) {
        $curr_servIDs[] = $curr_serv['id'];
    }

    // add new services
    foreach ($serviceIDs as $serviceID) {
        if (!in_array($serviceID, $curr_servIDs)) {
            $query = "insert into dbeventsservices (eventID, serviceID) values ('$eventID', '$serviceID')";
            $result = mysqli_query($connection, $query);
        }
    }
    // remove old services
    foreach ($curr_servIDs as $curr_serv) {
        if (!in_array($curr_serv, $serviceIDs)) {
            $query = "delete from dbeventsservices where serviceID='$curr_serv'";
            $result = mysqli_query($connection, $query);
        }
    }
    mysqli_commit($connection);
    return;
}

function find_event($nameLike)
{
    $connection = connect();
    $query = "
        select * from dbevents
        where name like '%$nameLike%'
    ";
    $result = mysqli_query($connection, $query);
    if (!$result) {
        return null;
    }
    $all = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_close($connection);
    return $all;
}

function fetch_events_in_date_range_as_array($start_date, $end_date)
{
    $connection = connect();
    $start_date = mysqli_real_escape_string($connection, $start_date);
    $end_date = mysqli_real_escape_string($connection, $end_date);
    $query = "select * from dbevents
              where date >= '$start_date' and date <= '$end_date'
              order by date, startTime asc";
    $result = mysqli_query($connection, $query);
    if (!$result) {
        mysqli_close($connection);
        return null;
    }
    $events = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_close($connection);
    return $events;
}

function fetch_all_events()
{
    $connection = connect();
    $query = "select * from dbevents
              order by date, startTime asc";
    $result = mysqli_query($connection, $query);
    if (!$result) {
        mysqli_close($connection);
        return null;
    }
    $events = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_close($connection);
    return $events;
}

function get_animal($id)
{
    $connection = connect();
    $query = "select * from dbanimals
              where id='$id'";
    $result = mysqli_query($connection, $query);
    if (!$result) {
        return [];
    }
    $animal = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_close($connection);
    return $animal;
}

function get_description($id)
{
    $connection = connect();
    $query = "select description from dbevents
              where id='$id'";
    $result = mysqli_query($connection, $query);
    if (!$result) {
        return [];
    }
    $description = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_close($connection);
    return $description;
}


function get_location($id)
{
    $connection = connect();
    $query = "select * from dblocations
              where id='$id'";
    $result = mysqli_query($connection, $query);
    if (!$result) {
        return [];
    }
    $location = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_close($connection);
    return $location;
}

function get_services($eventID)
{
    $connection = connect();
    $query = "select * from dbservices AS serv JOIN dbeventsservices AS es ON es.serviceID = serv.id
              where es.eventID='$eventID'";
    $result = mysqli_query($connection, $query);
    if (!$result) {
        return [];
    }
    $services = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_close($connection);
    return $services;
}

//TODO DELETE
// function attach_event_training_media($eventID, $url, $format, $description) {
//     return attach_media($eventID, 'training', $url, $format, $description);
// }

// function attach_post_event_media($eventID, $url, $format, $description) {
//     return attach_media($eventID, 'post', $url, $format, $description);
// }

// function detach_media($mediaID) {
//     $query = "delete from dbeventmedia where id='$mediaID'";
//     $connection = connect();
//     $result = mysqli_query($connection, $query);
//     mysqli_close($connection);
//     if ($result) {
//         return true;
//     }
//     return false;
// }

function delete_event($id)
{
    $query = "delete from dbevents where id='$id'";
    $connection = connect();
    $result = mysqli_query($connection, $query);
    $result = boolval($result);
    mysqli_close($connection);
    return $result;
}

function cancel_event($event_id, $account_name)
{
    $query = "DELETE from dbeventpersons where userID LIKE '$account_name' AND eventID LIKE $event_id";
    $connection = connect();
    $result = mysqli_query($connection, $query);
    $result = boolval($result);
    mysqli_close($connection);
    return $result;
}
/**
 * Approve a signup given a single event and a username
 * @param mixed $event_id The ID for the associated event
 * @param mixed $account_name The username for the associated account
 * @param mixed $position The position that the user applied for(DEPRECIATED)
 * @param mixed $notes Any notes for why the application was approved.
 * @return bool|mysqli_result
 */
function approve_signup($event_id, $account_name, $position, $notes)
{
    $connection = connect();
    $safe_event = mysqli_real_escape_string($connection, $event_id);
    $safe_user = mysqli_real_escape_string($connection, $account_name);
    $safe_pos = mysqli_real_escape_string($connection, $position);
    $safe_notes = mysqli_real_escape_string($connection, $notes);

    // 1. Delete from Pending (Using userID)
    $query = "DELETE FROM dbpendingsignups WHERE userID = '$safe_user' AND eventname = '$safe_event'";
    mysqli_query($connection, $query);

    // 2. Add to Active
    $query2 = "INSERT INTO dbeventpersons (eventID, userID, position, notes) VALUES ('$safe_event', '$safe_user', '$safe_pos', '$safe_notes')";
    $result2 = mysqli_query($connection, $query2);

    mysqli_commit($connection);

    if ($result2) {
        emailHandler($event_id, $account_name, 2, "Sign-up Approved.");
    }

    // mysqli_close($connection); // Optional, depending on if you reuse connection
    return $result2;
}

function approve_multiple_signups($event_id, $account_names, $notes = '')
{
    $approved = 0;
    if (!is_array($account_names) || empty($account_names)) return 0;

    foreach ($account_names as $account_name) {
        $ok = approve_signup($event_id, $account_name, 'Volunteer', $notes);
        if ($ok) {
            $approved++;
        }
    }
    return $approved;
}

/**
 * Reject a single sign up
 * @param mixed $event_id The Event ID
 * @param mixed $account_name The Account ID/Username
 * @param mixed $position The position or 'account type' of the user who applied.
 * @param mixed $notes Any notes on the rejection.
 * @return bool True if successfull, false if the rejection failed
 */
function reject_signup($event_id, $account_name, $position, $notes)
{
    $query = "DELETE from dbpendingsignups where username = '$account_name' AND eventname = '$event_id'";
    $connection = connect();
    $result = mysqli_query($connection, $query);
    $result = boolval($result);
    /*if ($result == true) Wrong number for email
    {
        emailHandler($event_id, $account_name, 2, "Sign-up DENIED.");
    }*/
    return $result;
}

function complete_event($id)
{
    $event = retrieve_event2($id);
    $animal = get_animal($event["animalID"])[0];
    $date = $event["date"];
    $event["completed"] = "yes";

    $services = get_services($event["id"]);
    $length = count($services);

    for ($i = 0; $i < $length; $i++) {
        $check = $services[$i]['name'];
        $dur = $services[$i]['duration_years'];
        if (stripos($check, "spay") !== false || stripos($check, "neuter") !== false) {
            $animal["spay_neuter_done"] = "yes";
            $animal["spay_neuter_date"] = $date;
        } else if (stripos($check, "rabie") !== false) {
            $animal["rabies_given_date"] = $date;
            $animal["rabies_due_date"] = date('Y-m-d', strtotime($date . "+" . $dur . " years"));
        } else if (stripos($check, "heartworm") !== false) {
            $animal["heartworm_given_date"] = $date;
            $animal["heartworm_due_date"] = date('Y-m-d', strtotime($date . "+" . $dur . " years"));
        } else if (stripos($check, "distemper 1") !== false) {
            $animal["distemper1_given_date"] = $date;
            $animal["distemper1_due_date"] = date('Y-m-d', strtotime($date . "+" . $dur . " years"));
        } else if (stripos($check, "distemper 2") !== false) {
            $animal["distemper2_given_date"] = $date;
            $animal["distemper2_due_date"] = date('Y-m-d', strtotime($date . "+" . $dur . " years"));
        } else if (stripos($check, "distemper 3") !== false) {
            $animal["distemper3_given_date"] = $date;
            $animal["distemper3_due_date"] = date('Y-m-d', strtotime($date . "+" . $dur . " years"));
        } else if (stripos($check, "microchip") !== false) {
            $animal["microchip_done"] = "yes";
        } else {
            $animal["notes"] = $animal["notes"] . " | " . $check . ": " . $date;
        }
    }
    //    var_dump($event);
    $result = update_animal2($animal);
    $result = update_event2($event["id"], $event);
    return $result;
}

function update_animal2($animal)
{
    $connection = connect();
    $id = $animal['id'];
    $odhsid = $animal["odhs_id"];
    $name = $animal["name"];
    $breed = $animal["breed"];
    $age = $animal["age"];
    $gender = $animal["gender"];
    $notes = $animal["notes"];
    $spay_neuter_done = $animal["spay_neuter_done"];
    $spay_neuter_date = $animal["spay_neuter_date"];
    if (empty($animal["spay_neuter_date"])) {
        $spay_neuter_date = '0000-00-00';
    }
    $rabies_given_date = $animal["rabies_given_date"];
    if (empty($animal["rabies_given_date"])) {
        $rabies_given_date = '0000-00-00';
    }
    $rabies_due_date = $animal["rabies_due_date"];
    if (empty($animal["rabies_due_date"])) {
        $rabies_due_date = '0000-00-00';
    }
    $heartworm_given_date = $animal["heartworm_given_date"];
    if (empty($animal["heartworm_given_date"])) {
        $heartworm_given_date = '0000-00-00';
    }
    $heartworm_due_date = $animal["heartworm_due_date"];
    if (empty($animal["heartworm_due_date"])) {
        $heartworm_due_date = '0000-00-00';
    }
    $distemper1_given_date = $animal["distemper1_given_date"];
    if (empty($animal["distemper1_given_date"])) {
        $distemper1_given_date = '0000-00-00';
    }
    $distemper1_due_date = $animal["distemper1_due_date"];
    if (empty($animal["distemper1_due_date"])) {
        $distemper1_due_date = '0000-00-00';
    }
    $distemper2_given_date = $animal["distemper2_given_date"];
    if (empty($animal["distemper2_given_date"])) {
        $distemper2_given_date = '0000-00-00';
    }
    $distemper2_due_date = $animal["distemper2_due_date"];
    if (empty($animal["distemper2_due_date"])) {
        $distemper2_due_date = '0000-00-00';
    }
    $distemper3_given_date = $animal["distemper3_given_date"];
    if (empty($animal["distemper3_given_date"])) {
        $distemper3_given_date = '0000-00-00';
    }
    $distemper3_due_date = $animal["distemper3_due_date"];
    if (empty($animal["distemper3_due_date"])) {
        $distemper3_due_date = '0000-00-00';
    }
    $microchip_done = $animal["microchip_done"];
    $query = "
        UPDATE dbanimals set odhs_id='$odhsid', name='$name', breed='$breed', age='$age', gender='$gender', notes='$notes', spay_neuter_done='$spay_neuter_done', spay_neuter_date='$spay_neuter_date', rabies_given_date='$rabies_given_date', rabies_due_date='$rabies_due_date', heartworm_given_date='$heartworm_given_date', heartworm_due_date='$heartworm_due_date', distemper1_given_date='$distemper1_given_date', distemper1_due_date='$distemper1_due_date', distemper2_given_date='$distemper2_given_date', distemper2_due_date='$distemper2_due_date', distemper3_given_date='$distemper3_given_date', distemper3_due_date='$distemper3_due_date', microchip_done='$microchip_done'
        where id='$id'
        ";
    $result = mysqli_query($connection, $query);
    if (!$result) {
        return null;
    }
    mysqli_commit($connection);
    mysqli_close($connection);
    return $id;
}

//There was a question mark followed by a > here

/** 
 * Gets the access level for each event to see if sign-up needs approval or not.
 * @param $event_id The id what we're querying. 
 * @return bool if true then the event requires approval for sign-up. if false then it does not.
 */
function fetch_signup_status(int $event_id): bool
{
    $connection = connect();

    $query = "SELECT access FROM dbevents WHERE id = $event_id";
    $result = mysqli_query($connection, $query);

    // Fetch the row
    $eventStatusRow = mysqli_fetch_assoc($result);

    // Return true/false based on the comparison
    return ($eventStatusRow['access'] == "Approval_Needed");
}

function getPAttendance($eventID)
{
    $conn = connect();

    $sql = "SELECT userID FROM dbeventpersons WHERE eventID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $eventID);
    $stmt->execute();
    $result = $stmt->get_result();

    $userIDs = [];

    while ($row = $result->fetch_assoc()) {
        $userIDs[] = $row['userID'];
    }

    return $userIDs;
}

// get all vehicles from the vehicles table
// TODO migrate to vehicles.php file once vehicle managment is added
function get_vehicles()
{
    $connection = connect();
    $query = "SELECT id, make_model, plate, capacity, wheelchair_accessible FROM vehicles ORDER BY make_model, plate";
    $result = mysqli_query($connection, $query);
    if (!$result) {
        mysqli_close($connection);
        return [];
    }
    $vehicles = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_close($connection);
    return $vehicles;
}

// get all vehicles with full details for display
function get_all_vehicles_full()
{
    $connection = connect();
    $query = "SELECT id, plate, vin, capacity, wheelchair_accessible, make_model, notes, created_at FROM vehicles ORDER BY make_model, plate";
    $result = mysqli_query($connection, $query);
    if (!$result) {
        mysqli_close($connection);
        return [];
    }
    $vehicles = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_close($connection);
    return $vehicles;
}

// delete a vehicle by id and  refuses if the vehicle is assigned to any trips
function delete_vehicle($id)
{
    $connection = connect();
    $id = (int) $id;
    $stmt = $connection->prepare("SELECT COUNT(*) FROM dbevents WHERE vehicle_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    if ($count > 0) {
        $connection->close();
        return false;
    }
    $stmt = $connection->prepare("DELETE FROM vehicles WHERE id = ?");
    $stmt->bind_param('i', $id);
    $success = $stmt->execute();
    $stmt->close();
    $connection->close();
    return $success;
}


// add a new vehicle to the sustem. 
function add_vehicle($plate, $capacity, $wheelchair_accessible, $make_model, $notes)
{
    $connection = connect();
    $stmt = $connection->prepare(
        "INSERT INTO vehicles (plate, capacity, wheelchair_accessible, make_model, notes)
         VALUES (?, ?, ?, ?, ?)"
    );
    if (!$stmt) {
        $connection->close();
        return false;
    }
    $stmt->bind_param('siiss', $plate, $capacity, $wheelchair_accessible, $make_model, $notes);
    $success = $stmt->execute();
    $new_id = $success ? $connection->insert_id : false;
    $stmt->close();
    $connection->close();
    return $new_id;
}

// assign a driver and vehicle to a dbEvnet and mark as Y - scheduled
//true on success, false on failure.
function assign_trip_driver_vehicle($eventID, $driver_id, $vehicle_id)
{
    $connection = connect();
    $eventID = (int) $eventID;
    $driver_id = (string) $driver_id;
    $vehicle_id = (int) $vehicle_id;
    $query = "UPDATE dbevents SET driver_id = ?, vehicle_id = ?, trip_status = 'scheduled', completed = 'Y' WHERE id = ?";
    $stmt = mysqli_prepare($connection, $query);

    mysqli_stmt_bind_param($stmt, 'sii', $driver_id, $vehicle_id, $eventID);
    $result = mysqli_stmt_execute($stmt);
    if (!$result) {
        mysqli_close($connection);
        return [];
    }

    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($connection);
    return $affected >= 0; // >= 0 so re-saving same values still counts as success, 
    //TODO change
}

function cancel_trip($eventID)
{
    $connection = connect();
    if (!$connection) return false;

    $eventID = (int) $eventID;
    $status = 'cancelled';

    $query = "UPDATE dbevents SET trip_status = ? WHERE id = ?";
    $stmt = mysqli_prepare($connection, $query);

    if (!$stmt) {
        mysqli_close($connection);
        return false;
    }

    mysqli_stmt_bind_param($stmt, 'si', $status, $eventID);
    $result = mysqli_stmt_execute($stmt);

    if (!$result) {
        mysqli_stmt_close($stmt);
        mysqli_close($connection);
        return false;
    }

    $affected = mysqli_affected_rows($connection);

    mysqli_stmt_close($stmt);
    mysqli_close($connection);

    return $affected > 0;
}

function dispatch_trip($eventID)
{
    $connection = connect();
    $eventID = (int) $eventID;
    $status = 'in_progress';

    $query = "UPDATE dbevents SET trip_status = ? WHERE id = ?";
    $stmt = mysqli_prepare($connection, $query);

    mysqli_stmt_bind_param($stmt, 'si', $status, $eventID);
    $result = mysqli_stmt_execute($stmt);
    if (!$result) {
        mysqli_stmt_close($stmt);
        mysqli_close($connection);
        return false;
    }

    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($connection);
    return $affected > 0;
}

function complete_trip($eventID, $mileage_start, $mileage_end)
{
    $connection = connect();
    $eventID = (int) $eventID;
    $status = 'completed';

    $query = "UPDATE dbevents SET
                trip_status = ?,
                mileage_start = ?,
                mileage_end = ?
            where id = ?";
    $stmt = mysqli_prepare($connection, $query);

    mysqli_stmt_bind_param($stmt, 'sssi', 
        $status, 
        $mileage_start, 
        $mileage_end, 
        $eventID
    );

    $result = mysqli_stmt_execute($stmt);
    if (!$result) {
        mysqli_stmt_close($stmt);
        mysqli_close($connection);
        return false;
    }

    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($connection);
    return $affected > 0;
}

function getFavoriteTripsByRiderId($riderId) {
    $con = connect();
    $query = "SELECT * FROM dbFavoriteTrips WHERE user_id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "s", $riderId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $favoriteTrips = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $favoriteTrips[] = $row;
    }
    mysqli_stmt_close($stmt);
    mysqli_close($con);
    return $favoriteTrips;
}

//this function is to save the entered trip into dbFavoriteTrips table for future use. It takes the user id, label, pickup location, dropoff location, dropoff contact and description as parameters and inserts a new record into the dbFavoriteTrips table. It returns true if the insertion is successful and false otherwise.
function saveFavoriteTrip($userId, $label, $pickupLocation, $dropoffLocation, $dropoffContact, $description) {
    $con = connect();
    $query = "INSERT INTO dbFavoriteTrips (user_id, label, pickup_location, dropoff_location, dropoff_contact, description) 
              VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "ssssss", $userId, $label, $pickupLocation, $dropoffLocation, $dropoffContact, $description);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($con);
    return $ok;
}

// //get event based on rider id
// function get_all_riders(){
//     $connection = connect();
//     $query = "SELECT * FROM dbevents WHERE rider_id = :rider_id";
//     $result = mysqli_query($connection, $query);

//     if(!$result){
//         mysqli_close($connection);
//         return [];
//     }

//     $riders = mysqli_fetch_all($result, MYSQLI_ASSOC);
//     mysqli_close($connection);
//     return $riders;
// }
