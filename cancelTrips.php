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
require_once('email.php');

$selectedDriver = trim((string) ($_POST['driver_id'] ?? ($event['driver_id'] ?? '')));
$selectedVehicle = (int) ($_POST['vehicle_id'] ?? ($event['vehicle_id'] ?? 0));
//include 'domain/Event.php';


function format_time_12h($time)
{
    $dt = DateTime::createFromFormat('H:i', $time);
    if ($dt instanceof DateTime) {
        return $dt->format('g:i A');
    }
    return $time;
}

function send_trip_cancelled_email($event)
{

    $con = connect();

    $stmt = mysqli_prepare($con, "SELECT Notifications FROM dbpersons WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "s", $event['rider_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    $riderNotif = (int)($row['Notifications'] ?? 0);

    if ($riderNotif !== 1) {
        return;
    }

    if (empty($event['rider_id'])) {
        return;
    }

    $rider = retrieve_person((string) $event['rider_id']);
    if (!$rider) {
        return;
    }

    $riderEmail = trim((string) $rider->get_email());
    if ($riderEmail === '') {
        return;
    }

    $subject = 'Trip Cancelled';
    $body = "Hello " . trim($rider->get_first_name() . ' ' . $rider->get_last_name()) . ",\n\n" .
        "Your trip has been cancelled.\n\n" .
        "Date: {$event['startDate']}\n" .
        "Time: " . format_time_12h((string) $event['startTime']) . " - " . format_time_12h((string) $event['endTime']) . "\n" .
        "Pickup: {$event['pickup_location']}\n" .
        "Dropoff: {$event['dropoff_location']}\n\n" .
        "If you need assistance with a replacement ride, please contact Healthy Generations.\n\n" .
        "Thank you,\n" .
        "Healthy Generations - Mobility Options";

    sendEmails([$riderEmail], 'Mobility Options', $subject, $body);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'])) {
    $eventID = (int) $_POST['event_id'];
    $action = $_POST['action_type'] ?? 'cancel';
    $eventToCancel = fetch_event_by_id($eventID);

    if ($action === 'no_show') {
        if (mark_no_show($eventID)) {
            header('Location: cancelTrips.php?status=noshow_success');
            exit;
        }
    } else {
        if (cancel_trip($eventID)) {
            send_trip_cancelled_email($eventToCancel);
            header('Location: cancelTrips.php?status=success');
            exit;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">


<style>
    .popup {
        position: fixed;
        inset: 0;
        background: rgba(0, 10, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }

    .popup-box {
        background: #fafafa;
        padding: 2rem;
        border-radius: 4px;
        text-align: center;
    }

    .popup-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin-top: 1rem;
    }

    .button.confirm {
        background: #C04000;
        font-size: larger;
    }

    .button.cancel {
        background: green;
        font-size: larger;

    }
</style>

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

    <h1>Cancel Trip</h1>
    <main class="general">
        <?php if (isset($_GET['status'])): ?>
            <div id="trip-status-toast" class="happy-toast">
                <?php
                if ($_GET['status'] === 'noshow_success') {
                    echo 'Trip Marked as No-Show';
                } else {
                    echo "Trip Cancelled";
                }
                ?>
            </div>
        <?php endif; ?>
        <?php
        //require_once('database/dbMessages.php');
        //$messages = get_user_messages($userID);
        //require_once('database/dbevents.php');
        //require_once('domain/Event.php');

        $events = get_all_events();
        $drivers  = get_drivers_with_email();
        $vehicles = get_vehicles();

        if (sizeof(get_all_events()) && sizeof($drivers)): ?>
            <div class="table-wrapper">
                <label> Select driver below to cancel scheduled trip:<br></label>
                <table class="general">
                    <thead>
                        <tr>
                            <th><b>Driver Name</b></th>
                            <th><b>Vehicle ID</b></th>
                            <th><b>Trip Date</b></th>
                            <th><b>Trip Time</b></th>
                            <th><b>Rider Name</b></th>
                            <th><b>Trip Status</b></th>
                            <th><b>Cancel Trip</b></th>
                        </tr>
                    </thead>
                    <?php
                    $vehicleMap = [];
                    foreach ($vehicles as $v) {
                        $vehicleMap[(int)$v['id']] = $v;
                    }
                    ?>
                    <tbody class="standout">
                        <?php foreach ($events as $event): ?>
                            <?php if ($event->getTripStatus() !== 'cancelled' && $event->getTripStatus() !== 'no_show' && $event->getTripStatus() !== 'completed'): ?>
                                <?php
                                $eventID = $event->getID();
                                $eventDate = $event->getStartDate();
                                $eventTime = format_time_12h($event->getStartTime());
                                $driverDI = $event->getDriverId();
                                $driverName = "";
                                $riderName = $event->getName();
                                $tripStatus = $event->getTripStatus();
                                foreach ($drivers as $driver) {
                                    if ($driver['id'] ==  $driverDI) {
                                        $driverName = $driver['first_name'] . ' ' . $driver['last_name'];
                                        break;
                                    }
                                }
                                $vehicleID = (int)$event->getVehicleId();
                                $vehicle = isset($vehicleMap[$vehicleID]) ? $vehicleMap[$vehicleID] : null;
                                ?>

                                <?php if ($accessLevel < 3): ?>
                                    <tr data-event-id="<?= $eventID ?>">
                                        <td><a href="event.php?id=<?= $eventID ?>"><?= $title ?></a></td>
                                        <td><?= $startDate ?></td>
                                        <td><a class="button sign-up" href="eventSignUp.php">Sign Up</a></td>
                                    </tr>
                                <?php else: ?>
                                    <tr data-event-id="<?= $eventID ?>">
                                        <td><?= $driverName ?></td>
                                        <td><?= $vehicle ? htmlspecialchars($vehicle['plate']) : 'no vehicle' ?></td>
                                        <td><?= $eventDate ?></td>
                                        <td><?= $eventTime ?></td>
                                        <td><?= $riderName ?></td>
                                        <td><?= htmlspecialchars(ucwords(str_replace('_', ' ', $tripStatus))) ?></td>

                                        <td>
                                            <a href="#" onclick="document.getElementById('popup<?= $eventID ?>').style.display='flex';" class="button confirm">
                                                Cancel Trip </a>
                                        </td>
                                    </tr>
                                    <div id="popup<?= $eventID ?>" class="popup" style="display:none;">
                                        <div class="popup-box">
                                            <h3>Update Trip Status</h3>
                                            <p>How would you like to cancel this trip?</p>
                                            <div class="popup-actions">
                                                <form method="POST" style="margin: 0;">
                                                    <input type="hidden" name="event_id" value="<?= $eventID ?>">
                                                    <input type="hidden" name="action_type" value="cancel">
                                                    <button type="submit" class="button confirm">Cancel Trip</button>
                                                </form>

                                                <form method="POST" style="margin: 0;">
                                                    <input type="hidden" name="event_id" value="<?= $eventID ?>">
                                                    <input type="hidden" name="action_type" value="no_show">
                                                    <button type="submit" class="button confirm">Mark As No-Show</button>
                                                </form>

                                                <a onclick="document.getElementById('popup<?= $eventID ?>').style.display='none';" class="button cancel">Go Back</a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <script>
            </script>
        <?php else: ?>
            <p class="no-events standout">There are currently no trips available to view.<a class="button add" href="addEvent.php">Create a New Trip</a> </p>
        <?php endif ?>
        <p class="no-events standout">
            <a class="button return" href="dispatchTrip.php">Return to Trip Management</a>
        </p>
    </main>
</body>

</html>