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
//include 'domain/Event.php';
?>
<!DOCTYPE html>
<html>

<head>
    <?php require_once('universal.inc') ?>
    <link rel="stylesheet" href="css/messages.css">
    </link>
    <script src="js/messages.js"></script>
    <title>Mobility Options | Ride Requests</title>
</head>

<style>
    .return-button {
        display: inline-block;
        width: 94%;
        color: #ffffff !important;
        background-color: #b44444;
        padding: 0.5rem 1.5rem;
        border: 3px solid rgba(255, 255, 255, 0.295);
        border-radius: 3rem;
        font-weight: 500;
        text-align: center;
        text-decoration: none;
        transition: background-color .3s;
        cursor: pointer;
    }
</style>

<body>
    <?php require_once('header.php') ?>
    <?php require_once('database/dbEvents.php'); ?>
    <h1>Rides</h1>
    <main class="general">
        <?php
        //require_once('database/dbMessages.php');
        //$messages = get_user_messages($userID);
        //require_once('database/dbevents.php');
        //require_once('domain/Event.php');
        $events = get_all_events();
        if (sizeof(get_all_events())): ?>
            <div class="table-wrapper">
                <label> Select riders name below to edit ride request record:<br></label>
                <table class="general">
                    <thead>
                        <tr>
                            <th><b>Rider Name</b></th>
                            <th><b>Date Of Ride</b></th>

                            <th style="width:1px"></th>
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
                            // $capacity = $event->getCapacity();
                            $completed = $event->getCompleted();

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
                                            <td>$startDate</td>
                                            
                                        </tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="no-events standout">There are currently no ride requests available to view.<a class="button add" href="addEvent.php">Create a New Event</a> </p>
        <?php endif ?>
        <center><a class="return-button" href="eventManagement.php">Return to Ride Management</a></center>
    </main>
</body>

</html>