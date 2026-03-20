<?php
// Template for new VMS pages. Base your new page on this one

// Make session information accessible, allowing us to associate
// data with the logged-in user.
session_cache_expire(30);
session_start();

$loggedIn = false;
$accessLevel = 0;
$userID = null;
if (isset($_SESSION['_id'])) {
    $loggedIn = true;
    // 0 = not logged in, 1 = standard user, 2 = manager (Admin), 3 super admin (TBI)
    $accessLevel = $_SESSION['access_level'];
    $userID = $_SESSION['_id'];
}
include 'database/dbEvents.php';
include 'database/dbPersons.php';

//include 'domain/Event.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Popup on Button Click in TD</title>
    <style>
        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 10px;
            border: 1px solid #ccc;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            text-align: center;
        }

        .popup:target {
            display: block;
        }
    </style>
</head>

<head>
    <?php require_once('universal.inc') ?>
    <link rel="stylesheet" href="css/messages.css">
    </link>
    <script src="js/messages.js"></script>
    <title>Mobility Options | Ride Requests</title>
</head>

<body>
    <?php require_once('header.php') ?>
    <?php require_once('database/dbEvents.php'); ?>
    <?php require_once('database/dbPersons.php'); ?>

    <h1>Driver and Trips</h1>
    <main class="general">
        <?php
        //require_once('database/dbMessages.php');
        //$messages = get_user_messages($userID);
        //require_once('database/dbevents.php');
        //require_once('domain/Event.php');

        $events = get_all_events();
        $drivers  = get_drivers();

        if (sizeof(get_all_events()) && sizeof($drivers)): ?>
            <div class="table-wrapper">
                <label> Select riders name below to edit ride request record:<br></label>
                <table class="general">
                    <thead>
                        <tr>
                            <th><b>Driver Name</b></th>
                            <th><b>Pickup Time</b></th>
                            <th><b>Pickup Location</b></th>
                            <th><b>Drop-Off Location</b></th>
                            <th><b>Riders to </b></th>


                            <!-- <th style="width:1px"></th> -->
                        </tr>
                    </thead>
                    <tbody class="standout">
                        <?php
                        // require_once('database/dbPersons.php');
                        // require_once('include/output.php');
                        // $id_to_name_hash = [];
                        foreach ($events as $event) {
                            $eventID = $event->getID();
                            $title = $event->getName();
                            $startDate = $event->getStartDate();
                            $startTime = $event->getStartTime();
                            $endTime = $event->getEndTime();
                            $description = $event->getDescription();
                            $pickupLocation = $event->getPickupLocation();
                            $dropoffLocation = $event->getDropoffLocation();
                            $driverDI = $event->getDriverId();
                            // $capacity = $event->getCapacity();
                            $completed = $event->getCompleted();

                            foreach ($drivers as $driver) {
                                // $driverName = $driver->get();

                                if ($accessLevel < 3) {
                                    echo "
                                        <tr data-event-id='$eventID'>
                                            <td><a href='event.php?id=$eventID'>$title</a></td> <!-- Link updated here -->
                                            <td>$date</td>
                                            <td><a class='button sign-up' href='eventSignUp.php'>Sign Up</a></td>
                                        </tr>";
                                } else {
                                    echo "
                                        <tr data-event-id='$eventID'>
                                            <td><a href='event.php?id=$eventID' style='color: black; text-decoration: underline;'>$title</a></td> <!-- Link updated here -->
                                            <td>$startTime</td>
                                            <td>$pickupLocation</td>
                                            <td>$dropoffLocation</td> 
                                            <td><a href='#popup$eventID' style='color: black; text-decoration: underline;'>$description</a> </td>
                                        </tr>
                                         ";
                                    echo "
                                        <div id='popup$eventID' class='popup'>
                                            <p>$description</p>
                                            <a href='#'>Close</a>
                                        </div>
                                        ";
                                }
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <script>
            </script>
        <?php else: ?>
            <p class="no-events standout">There are currently no trips available to view.<a class="button add" href="">Create a New Trip</a> </p>
        <?php endif ?>
        <a class="button return" href="eventManagement.php">Return to Dashboard</a>
    </main>
</body>

</html>