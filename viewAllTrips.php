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

function send_trip_dispatched_email($event) {
    if (empty($event['rider_id'])) { return; }

    $rider = retrieve_person((string) $event['rider_id']);
    if (!$rider) { return; }

    $riderEmail = trim((string) $rider->get_email());
    if (!$riderEmail) { return; }

    $driverName = 'Assigned Driver';
    if (!empty($event['driver_id'])) {
        $driver = retrieve_person((string) $event['driver_id']);
        if ($driver) { $driverName = trim($driver->get_first_name() . ' ' . $driver->get_last_name()); }
    }

    $vehicleLabel = 'Vehicle';
    if (!empty($event['vehicle_id'])) {
        $vehicleLabel = 'Vehicle ID: ' . (int) $event['vehicle_id'];
        foreach (get_vehicles() as $vehicle) {
            if ((int) $vehicle['id'] === (int) $event['vehicle_id']) {
                $makeModel = (string) ($vehicle['make_model']);
                $plate = (string) ($vehicle['plate']);
                $vehicleLabel = $makeModel . (' [ID: ' . $plate . ']');
                break;
            }
        }
    }

    $subject = 'Trip Dispatched';
    $body = "Hello " . trim($rider->get_first_name() . ' ' . $rider->get_last_name()) . ",\n\n" .
        "Your scheduled ride has been dispatched and is now in progress with the following details:\n\n" .
        "Date: {$event['startDate']}\n" .
        "Time: " . format_time_12h((string) $event['startTime']) . " - " . format_time_12h((string) $event['endTime']) . "\n" .
        "Pickup: {$event['pickup_location']}\n" .
        "Dropoff: {$event['dropoff_location']}\n" .
        "Driver: {$driverName}\n" .
        "Vehicle: {$vehicleLabel}\n\n" .
        "Thank you,\n" .
        "Healthy Generations - Mobility Options";

    sendEmails([$riderEmail], 'Mobility Options', $subject, $body);
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $eventID = (int)$_GET['id'];
    $event = fetch_event_by_id($eventID);
    if ($event && dispatch_trip($eventID)) {
        send_trip_dispatched_email($event);
        header("Location: viewAllTrips.php?status=success");
        exit;
    }
}

function format_time_12h($time) {
    $dt = DateTime::createFromFormat('H:i', $time);
    if ($dt instanceof DateTime) {  return $dt->format('g:i A'); }
    return $time;
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
        background: green;
        font-size: larger;
    }

    .button.cancel {
        background: #C04000;
        font-size: larger;

    }
    .return-button {
        display: inline-block;
        width: 94%;
        color: #ffffff !important;
        background-color: #b44444;
        padding: var(--button-padding);
        border: 3px solid rgba(255, 255, 255, 0.295);
        border-radius: var(--button-border-radius);
        font-weight: 500;
        text-align: center;
        text-decoration: none;
        transition: background-color .3s;
        cursor: pointer;
    }
</style>

<head>
    <?php require_once('universal.inc') ?>
    <link rel="stylesheet" href="css/messages.css">
    </link>
    <script src="js/messages.js"></script>
    <title>Mobility Options | Dispatch Trip </title>
</head>

<body>
    <?php require_once('header.php') ?>
    <?php require_once('database/dbEvents.php'); ?>
    <?php require_once('database/dbPersons.php'); ?>

    <h1>Dispatch Trip</h1>
    <main class="general">
        <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
            <div id="trip-status-toast" class="happy-toast">Trip Dispatched!</div>
            <script>
                setTimeout(function() {
                    const toast = document.getElementById('trip-status-toast');
                    if (toast) {
                        toast.style.display = 'none';
                    }
                }, 1200);
            </script>
        <?php endif; ?>
        <?php
        $events = get_all_events();
        $drivers  = get_drivers_with_email();
        $vehicles = get_vehicles();

        if (sizeof(get_all_events()) && sizeof($drivers)): ?>
            <div class="table-wrapper">
                <label> Select the Driver you would like to dispatch below:<br></label>
                <table class="general">
                    <thead>
                        <tr>
                            <th><b>Driver Name</b></th>
                            <th><b>Vehicle ID</b></th>
                            <th><b>Trip Date</b></th>
                            <th><b>Trip Time</b></th>
                            <th><b>Notification</b></th>
                            <th><b>Dispatch</b></th>

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
                            <?php if ($event->getDriverId() !== null): ?>
                                <?php
                                $eventID = $event->getID();
                                $eventDate = $event->getStartDate();
                                $eventTimeRaw = $event->getStartTime();
                                $eventTime = format_time_12h($eventTimeRaw);
                                $driverDI = $event->getDriverId();
                                $tripStatus = $event->getTripStatus();
                                $driverName = "";
                                $riderName = $event->getName();
                                $alertFlag = '';

                                $tripDateTime = strtotime(trim((string)$eventDate . ' ' . (string)$eventTimeRaw));
                                if ($tripDateTime !== false) {
                                    if ($tripDateTime < time()) {
                                        $alertFlag = "<span style='display:inline-block;padding:4px 8px;border-radius:999px;background:#fff1f0;color:#c62828;font-weight:700;font-size:.8rem;border:1px solid #ef9a9a;'>OVERDUE</span>";
                                    } elseif ($eventDate === date('Y-m-d')) {
                                        $alertFlag = "<span style='display:inline-block;padding:4px 8px;border-radius:999px;background:#fff8e1;color:#8a6d1f;font-weight:700;font-size:.8rem;border:1px solid #f0c36d;'>ON THIS DATE</span>";
                                    }
                                }

                                if ($tripStatus !== 'scheduled') continue;
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
                                        <td><a href="event.php?id=<?= $eventID ?>"><?= $riderName ?></a></td>
                                        <td><?= $startDate ?></td>
                                        <td><a class="button sign-up" href="eventSignUp.php">Sign Up</a></td>
                                    </tr>
                                <?php else: ?>
                                    <tr data-event-id="<?= $eventID ?>">
                                        <td><?= $driverName ?></td>
                                        <td><?= $vehicle ? htmlspecialchars($vehicle['plate']) : 'no vehicle' ?></td>
                                        <td><?= $eventDate ?></td>
                                        <td><?= $eventTime ?></td>
                                        <td><?= $alertFlag ?></td>
                                        

                                            <!-- <td>
                                            <a href="#" onclick="window.location.href = 'viewPassengers.php'" style.display='flex' ; style="color: black; text-decoration: underline;">
                                                Passenger List </a>
                                        </td> -->
                                        <td>
                                            <a href="#" onclick="document.getElementById('popup<?= $eventID ?>').style.display='flex';" class="button confirm">
                                                Dispatch Trip </a>
                                        </td>
                                    </tr>

                                    <div id="popup<?= $eventID ?>" class="popup" style="display:none;">
                                        <div class="popup-box">
                                            <p>Are you sure you want to dispatch this trip?</p>
                                            <div class="popup-actions">
                                                <a href="viewAllTrips.php?id=<?= $eventID ?>" class="button confirm">Confirm</a>
                                                <a onclick="document.getElementById('popup<?= $eventID ?>').style.display='none';" class="button cancel">Cancel</a>
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
            <p class="no-events standout"> There are currently no trips available to view.<a class="button add" href="addEvent.php">Create a New Trip</a> </p>
        <?php endif ?>
        <p class="no-events standout">
            <a class="return-button" href="dispatchTrip.php">Return to Trip Management</a>
            </p>
    </main>
</body>

</html>