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


require_once('database/dbEvents.php');
require_once('database/dbPersons.php');
require_once('include/input-validation.php');



$eventID = $_GET['id'] ?? $_POST['id'] ?? null;
$event = fetch_event_by_id($eventID);

$errors = [];

$selectedDriver = trim((string) ($_POST['driver_id'] ?? ($event['driver_id'] ?? '')));
$selectedVehicle = (int) ($_POST['vehicle_id'] ?? ($event['vehicle_id'] ?? 0));

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign'])) {
    $driver_id  = trim($_POST['driver_id']); // dbpersons id
    $vehicle_id = (int) ($_POST['vehicle_id']); // vehicle id

    if ($driver_id === '' ) { $errors[] = 'Please select a driver.'; }
    if ($vehicle_id <= 0) { $errors[] = 'Please select a vehicle.'; }

function driver_has_time_conflict($driver_id, $startDate, $startTime, $eventID) {
    $con = connect();

    $query = "SELECT id, driver_id, startDate, startTime
              FROM dbevents
              WHERE driver_id = ?
                AND startDate = ?
                AND id != ?
                AND ABS(TIME_TO_SEC(TIMEDIFF(startTime, ?))) < 1800
              LIMIT 1";

    $stmt = mysqli_prepare($con, $query);

    if (!$stmt) {
        die('Prepare failed: ' . mysqli_error($con));
    }

    mysqli_stmt_bind_param($stmt, 'ssis', $driver_id, $startDate, $eventID, $startTime);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    return mysqli_num_rows($result) > 0;
}

function vehicle_has_time_conflict($vehicle_id, $startDate, $startTime, $eventID) {
    $con = connect();

    $query = "SELECT id, vehicle_id, startDate, startTime
              FROM dbevents
              WHERE vehicle_id = ?
                AND startDate = ?
                AND id != ?
                AND ABS(TIME_TO_SEC(TIMEDIFF(startTime, ?))) < 1800
              LIMIT 1";

    $stmt = mysqli_prepare($con, $query);

    if (!$stmt) {
        die('Prepare failed: ' . mysqli_error($con));
    }

    mysqli_stmt_bind_param($stmt, 'isis', $vehicle_id, $startDate, $eventID, $startTime);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    return mysqli_num_rows($result) > 0;
}

    if (empty($errors)) {
    $tripDate = $event['startDate'];
    $tripTime = $event['startTime'];


    
    if (driver_has_time_conflict($driver_id, $tripDate, $tripTime, $eventID)) {
        $errors[] = 'This driver is already scheduled for another trip within 30 minutes of this time.';
        
    }
    else if(vehicle_has_time_conflict($vehicle_id, $tripDate, $tripTime, $eventID)){
        $errors[] = 'This vehicle is already scheduled for another trip within 30 minutes of this time.';
    } else {
        $ok = assign_trip_driver_vehicle($eventID, $driver_id, $vehicle_id);

        if ($ok) {
            header('Location: viewAllEvents.php');
            exit;
        } else {
            $errors[] = 'Could not schedule request!';
        }
    }
}
}



function val($key, $fallback = '') {
    global $event;
    return ($event[$key] ?? $fallback);
}

// stole this - formats time
function format_time_12h($time) {
    $dt = DateTime::createFromFormat('H:i', $time);
    if ($dt instanceof DateTime) {  return $dt->format('g:i A'); }
    return $time;
}

$drivers  = get_drivers(); // get all drivers for drop donw
$vehicles = get_vehicles(); // get all vehicles for drop down

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once('universal.inc'); ?>
    <title>Mobility Options | Schedule Trip</title>
</head>
<body>
    <?php require_once('header.php'); ?>

    <main class="general">
        <h1>Ride Scheduler</h1>
    
        

    <?php if (!empty($errors)): ?>
<script>
    alert("<?php echo implode('\n', $errors); ?>");
</script>
<?php endif; ?>

        <form method="POST" class="general">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($eventID); ?>">

            <section>
                <h2>Ride Request Details</h2>
                <br/>
                <p><strong>Rider:</strong> <?php echo val('name'); ?></p>
                <p><strong>Date:</strong> <?php echo val('startDate'); ?></p>
                <p><strong>Time:</strong> <?php echo format_time_12h(val('startTime')); ?> 
                &ndash; <?php echo format_time_12h(val('endTime')); ?></p>
                <p><strong>Pickup:</strong> <?php echo val('pickup_location'); ?></p>
                <p><strong>Dropoff:</strong> <?php echo val('dropoff_location'); ?></p>
                <p><strong>Notes:</strong> <?php echo val('description'); ?></p>
            </section>

            <section>
                <br/>
                <h2>Assign Driver & Vehicle</h2>
                <br/>
                <div>
                    <label for="driver_id"><strong>Driver</strong></label><br>
                    <select name="driver_id" id="driver_id" required>
                        <option value="">Select a driver</option>
                        <?php foreach ($drivers as $driver):
                            $driverID   = trim((string) $driver['id']);
                            $driverName = $driver['first_name'] . ' ' . $driver['last_name'];
                            $selected   = (strcasecmp($driverID, $selectedDriver) === 0) ? 'selected' : '';
                        ?>
                            <option value="<?php echo $driverID; ?>" <?php echo $selected; ?>><?php echo $driverName; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (empty($drivers)): ?>
                        <p >No drivers found in the system.</p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="vehicle_id"><strong>Vehicle</strong></label><br>
                    <select name="vehicle_id" id="vehicle_id" required>
                        <option value="">Select a vehicle</option>
                        <?php foreach ($vehicles as $vehicle):
                            $vID = (int) $vehicle['id'];
                            $vLabel = $vehicle['make_model'] .
                            ' [' . $vehicle['plate'] . ']' .
                            ' — Capacity: ' . $vehicle['capacity'] .
                            ' — Wheelchair Accessible: ' .
                             ($vehicle['wheelchair_accessible'] ? ' Yes' : 'No');
                            $selected  = ($vID === $selectedVehicle) ? 'selected' : '';
                        ?>
                            <option value="<?php echo $vID; ?>" <?php echo $selected; ?>><?php echo $vLabel; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (empty($vehicles)): ?>
                        <p>No vehicles found in the system.</p>
                    <?php endif; ?>
                </div>
            </section>

            <div style="margin-top:1rem; display:flex; gap:0.75rem;">
                <button type="submit" name="assign" class="button add">Schedule Trip</button>
                <a class="button cancel" href="viewAllEvents.php">Back to list</a>
                <a class="button" href="calendar.php">Calendar</a>
            </div>
            <div style="margin-top:2rem; width:span; display:flex; gap:0.75rem;">
                
            </div>
        </form>

    </main>
</body>
</html>
