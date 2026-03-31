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
    <?php require_once('database/dbPersons.php'); ?>
    
    <h1>Complete Trip</h1>
    <main class="general">
        <?php
        $events = get_all_prog_events();
        $drivers = get_drivers_with_email();
        $vehicles = get_vehicles();

        if (sizeof(get_all_prog_events()) && sizeof($drivers)): ?>
            <div class="table-wrapper">
                <label>Finalize dispatched trips for reporting by selecting "Complete".</label>
                <table class="general">
                    <thead>
                        <tr>
                            <th><b>Trip Date</b></th>
                            <th><b>Trip Time</b></th>
                            <th><b>Assigned Driver</b></th>
                            <th><b>Assigned Rider</b></th>
                            <th><b>Complete Trip</b></th>
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
                                $eventTime = $event->getStartTime();
                                $driverDI = $event->getDriverId();
                                $tripStatus = $event->getTripStatus();
                                $driverName = "";
                                $riderName = $event->getName();

                                if ($tripStatus !== 'in_progress') continue;
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
                                        <td><?= $eventDate ?></td>
                                        <td><?= $eventTime ?></td>
                                        <td><?= $driverName ?></td>
                                        <th><?= $riderName ?></td>


                                            <!-- <td>
                                            <a href="#" onclick="window.location.href = 'viewPassengers.php'" style.display='flex' ; style="color: black; text-decoration: underline;">
                                                Passenger List </a>
                                        </td> -->
                                        <td>
                                            <a href="#" onclick="document.getElementById('popup<?= $eventID ?>').style.display='flex';" class="button confirm">
                                                Complete Trip </a>
                                        </td>
                                    </tr>

                                    <div id="popup<?= $eventID ?>" class="popup" style="display:none;">
                                        <div class="popup-box">
                                            <p>Are you sure you want to complete this trip?</p>
                                            <div class="popup-actions">
                                                <a href="completeTrip.php?id=<?= $eventID ?>" class="button confirm">Confirm</a>
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
        <?php else: ?>
            <p class="no-events standout">There are currently no dispatched trips to complete.<a class="button add" href="viewAllTrips.php">Dispatch a Trip</a> </p>
        <?php endif ?>
        <a class="button return" href="index.php">Return to Dashboard</a>
    </main>
</body>

</html>