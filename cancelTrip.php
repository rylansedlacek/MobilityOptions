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
$action = $_GET['action'] ?? 'cancel';

$event = fetch_event_by_id($eventID);

if(!$event) {
    die("Trip not found.");
}

if($action === 'no_show') {
    if(mark_no_show($eventID)) {
        header("Location: cancelTrips.php?status=noshow_success");
        exit;
    } else {
        die("Failed to mark as no-show. Please try again.");
    }
} else {
    if(cancel_trip($eventID)) {
    header("Location: cancelTrips.php?status=success");
    exit;
    }

    else {
        die("Failed to cancel the trip. Please try again.");
    }
}