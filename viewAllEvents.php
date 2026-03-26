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
    <title>Mobility Options | View Rides</title>
</head>

<body>
    <?php require_once('header.php') ?>
    <?php require_once('database/dbEvents.php'); ?>
    <h1>Ride Requests</h1>
    <main class="general">
        <?php
        //require_once('database/dbMessages.php');
        //$messages = get_user_messages($userID);
        //require_once('database/dbevents.php');
        //require_once('domain/Event.php');
        $events = get_pending_ride_requests();
        if (sizeof($events)): ?>
            <div class="table-wrapper">
                <label> Click Schedule to schedule request.</label>
                <table class="general">
                    <thead>
                        <tr>
                            <th>Rider Name</th>
                            <th>Date Of Ride</th>
                            <th>Scheduled?</th>
                            <th>Schedule Trip</th>
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
                            $tripStatus = $event->getTripStatus() ?: 'N';

                            $viewLink = "<a href='event.php?id=$eventID'>$title</a>";
                            $scheduleLink = "";
                            if ($accessLevel >= 2) {
                                $scheduleLink = "<a class='button add' href='scheduleTrip.php?id=$eventID'>Schedule</a>";
                            }

                            echo "
                                <tr data-event-id='$eventID'>
                                <td><a href='event.php?id=$eventID' style='color: black; text-decoration: underline;'>$title</a></td> <!-- Link updated here -->
                                    <td>$startDate</td>
                                    <td>$tripStatus</td>
                                    <td>$scheduleLink</td>
                                    <td></td>
                                </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="no-events standout">There are currently no requests available to view.<a class="button add" href="addEvent.php">Create a New Event</a> </p>
        <?php endif ?>
        <a class="button" href="viewAllTrips.php">Dispatch Trips</a>
        <a class="button cancel" href="eventManagement.php">Return to Dashboard</a>
        
    </main>
</body>

</html>