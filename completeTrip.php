<?php
session_start();
include 'database/dbEvents.php'; 

if(!isset($_SESSION['_id']) || $_SESSION['access_level'] < 2) {
   die("Access denied.");
}

if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid trip ID.");
}

$eventID = (int)$_GET['id'];

$event = fetch_event_by_id($eventID);
if(!$event) {
    die("Trip not found.");
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mileage_start = $_POST['start_mileage'];
    $mileage_end   = $_POST['end_mileage'];

    if(complete_trip($eventID, $mileage_start, $mileage_end)) {
        header("Location: completeTripForm.php?id=" . urlencode((string)$eventID) . "&createSuccess=1");
        exit;
    } else {
        die("Failed to complete the trip. Please try again.");
    }
} else {
    die("Invalid request method.");
}
?>