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

function format_time_12h($time)
{
    $dt = DateTime::createFromFormat('H:i', $time);
    if ($dt instanceof DateTime) {
        return $dt->format('g:i A');
    }
    return $time;
}

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
                <label> Click "Schedule" to schedule ride request.</label>
                <table class="general">
                    <thead>
                        <tr>
                            <th>Rider Name</th>
                            <th>Date of Ride</th>
                            <th>Time of Ride</th>
                            <th>Notification</th>
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
                            $startTimeRaw = $event->getStartTime();
                            $startTime = format_time_12h($startTimeRaw);
                            $tripStatus = $event->getTripStatus() ?: 'N';

                            $isUnscheduled = ($tripStatus === 'N' || $tripStatus === 'requested');
                            $alertFlag = '';
                            if ($tripStatus == 'N') $tripStatus = "Not Scheduled";

                            $rideDateTime = strtotime(trim((string) $startDate . ' ' . (string) $startTimeRaw));
                            if ($isUnscheduled && $rideDateTime !== false) {
                                $secondsUntilRide = $rideDateTime - time();
                                if ($secondsUntilRide < 0) {
                                    $alertFlag = "<span style='display:inline-block;padding:4px 8px;border-radius:999px;background:#fff1f0;color:#c62828;font-weight:700;font-size:.8rem;border:1px solid #ef9a9a;'>OVERDUE</span>";
                                } elseif ($secondsUntilRide <= 86400) {
                                    $alertFlag = "<span style='display:inline-block;padding:4px 8px;border-radius:999px;background:#fff8e1;color:#8a6d1f;font-weight:700;font-size:.8rem;border:1px solid #f0c36d;'>24hrs til</span>";
                                }
                            }

                            $viewLink = "<a href='event.php?id=$eventID'>$title</a>";
                            $scheduleLink = "";
                            if ($accessLevel >= 2) {
                                $scheduleLink = "<a class='button add' href='scheduleTrip.php?id=$eventID'>Schedule</a>";
                            }

                            echo "
                                <tr data-event-id='$eventID'>
                                <td><a href='event.php?id=$eventID' style='color: black; text-decoration: underline;'>$title</a></td> <!-- Link updated here -->
                                    <td>$startDate</td>
                                    <td>$startTime</td>
                                    <td>$alertFlag</td>
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

        <a class="button" style="display: inline-flex; height: 48px; align-items: center; justify-content: center;" href="viewAllTrips.php">Dispatch Trips</a>
        <a class="return-button" href="eventManagement.php">Return to Ride Management</a>

    </main>
</body>

</html>