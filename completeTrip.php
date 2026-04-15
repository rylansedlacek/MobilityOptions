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
    $completion_status = isset($_POST['completion_status']) ? $_POST['completion_status'] : '';

    if (strtolower($completion_status) === 'no show') {
        $connection = connect();
        $status = 'no_show';
        $query = "UPDATE dbevents SET trip_status = ? WHERE id = ?";
        $stmt = mysqli_prepare($connection, $query);
        mysqli_stmt_bind_param($stmt, 'si', $status, $eventID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        mysqli_close($connection);

        header("Location: completeTripForm.php?id=" . urlencode((string)$eventID) . "&createSuccess=1");
        exit;
    }

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