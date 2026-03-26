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
    <title>Mobility Options | Complete Trip</title>
</head>

<body>
    <?php require_once('header.php') ?>
    <?php require_once('database/dbEvents.php'); ?>
    <h1>Complete Trip</h1>
    <main class="general">
        <?php
        //require_once('database/dbMessages.php');
        //$messages = get_user_messages($userID);
        //require_once('database/dbevents.php');
        //require_once('domain/Event.php');
        $events = get_all_events();
        if (sizeof(get_all_events())): ?>
            <div class="table-wrapper">
                <label>Finalize the ride by selecting "Complete".</label> <br/>
                <table class="general">
                    <thead>
                        <tr>
                            <th>Rider Name</th>
                            <th>Date Of Ride</th>
                            <th>Pick Up Time</th>
                            <th>Complete Trip</th>
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
                            $completeLink = "";
                            if ($accessLevel >= 2) {
                                $completeLink = "<a class='button add' href='CompleteTripForm.php?id=$eventID'>Complete</a>";
                            }

                            echo "
                                <tr data-event-id='$eventID'>
                                <td><a href='event.php?id=$eventID' style='color: black; text-decoration: underline;'>$title</a></td> <!-- Link updated here -->
                                    <td>$startDate</td>
                                    <td>$tripStatus</td>
                                    <td>$completeLink</td>
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
        <div class="text-center mt-6">
        <a class="button return" href="dispatchTrip.php">Return to Dashboard</a>
        </div>
    </main>
</body>

</html>