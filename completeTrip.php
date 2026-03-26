<?php
session_start();
include 'database/dbEvents.php'; 

if(!isset($_SESSION['_id']) || $_SESSION['access_level'] < 2) {
   die("Access denied. ");
}

if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid trip ID.");
}

$eventID = (int)$_GET['id'];

$event = fetch_event_by_id($eventID);
if(!$event) {
    die("Trip not found.");
}

if(complete_trip($eventID)) {
    header("Location: completeTrips.php?status=success");
    exit;
}

else {
    die("Failed to complete the trip. Please try again.");
}
?>