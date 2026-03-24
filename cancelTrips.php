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

$selectedDriver = trim((string) ($_POST['driver_id'] ?? ($event['driver_id'] ?? '')));
$selectedVehicle = (int) ($_POST['vehicle_id'] ?? ($event['vehicle_id'] ?? 0));
//include 'domain/Event.php';
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
                <label> Select riders name below to edit ride request record:<br></label>
                <table class="general">
                    <thead>
                        <tr>
                            <th><b>Driver Name</b></th>
                            <th><b>Vehicle Model</b></th>
                            <th><b>Vehicle Plate</b></th>
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
                            <?php if ($event->getTripStatus() === 'scheduled' && $event->getCompleted() === 'Y'): ?>
                                <?php
                                $eventID = $event->getID();
                                $driverDI = $event->getDriverId();
                                $driverName = "";
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
                                        <td><?= $vehicle ? htmlspecialchars($vehicle['make_model']) : 'no vehicle' ?></td>
                                        <td><?= $vehicle ? htmlspecialchars($vehicle['plate']) : 'no vehicle' ?></td>
                                        <td>
                                            <a href="#" onclick="document.getElementById('popup<?= $eventID ?>').style.display='flex';" style="color: black; text-decoration: underline;">
                                                Cancel Trip</a>
                                        </td>
                                    </tr>
                                    <div id="popup<?= $eventID ?>" class="popup" style="display:none;">
                                        <div class="popup-box">
                                            <p>Are you sure you want to cancel this trip?</p>
                                            <div class="popup-actions">
                                                <a href="cancelTrip.php?id=<?= $eventID ?>" class="button confirm">Cancel Trip</a>
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
        <a class="button return" href="dispatchTrip.php">Return to Dashboard</a>
        </p>
    </main>
</body>

</html>