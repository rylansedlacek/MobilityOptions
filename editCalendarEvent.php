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


require_once('include/input-validation.php');
require_once('include/output.php');
require_once('include/trip-workflow.php');
require_once('database/dbEvents.php');
require_once('database/dbPersons.php');

function update_calendar_event_details($eventID, $eventDetails) {
    $connection = connect();
    if (!$connection) {
        return false;
    }

    $query = 'update dbevents
        set name = ?, startDate = ?, endDate = ?, startTime = ?, endTime = ?, description = ?,
        pickup_location = ?, dropoff_location = ?, dropoff_contact = ?
        where id = ?';
    $stmt = mysqli_prepare($connection, $query);
    if (!$stmt) {
        mysqli_close($connection);
        return false;
    }

    mysqli_stmt_bind_param(
        $stmt,
        'sssssssssi',
        $eventDetails['name'],
        $eventDetails['date'],
        $eventDetails['date'],
        $eventDetails['start-time'],
        $eventDetails['end-time'],
        $eventDetails['description'],
        $eventDetails['pickup_location'],
        $eventDetails['dropoff_location'],
        $eventDetails['dropoff_contact'],
        $eventID
    );

    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($connection);

    return $success;
}


function field_value($postKey, $eventKey, $default = '') {
    global $event;
    if (isset($_POST[$postKey])) { return $_POST[$postKey]; }
    return $event[$eventKey] ?? $default;
}

function status_text($status) {
    if ($status === 'scheduled') { return 'Ride Request Updated and Scheduled.'; }
    return 'Ride Request Updated.';
}

$errors = '';
$eventID = $_GET['id'] ?? $_POST['id'] ?? null;
if (!$eventID) {  die(); }

$event = fetch_event_by_id($eventID);
if (!$event) {
    echo 'Request does not exist';
    die();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $args = sanitize($_POST, null);
    $required = [
        'id',
        'name',
        'date',
        'start-time',
        'end-time',
        'description',
    ];

    if (!wereRequiredFieldsSubmitted($args, $required, false)) {
        $errors .= '<p>Your request was missing arguments.</p>';
    } else {
        $validatedTimes = validate12hTimeRangeAndConvertTo24h($args['start-time'], $args['end-time']);
        if (!$validatedTimes) {
            $errors .= '<p>The provided time range was invalid.</p>';
        }

        $date = validateDate($args['date']);
        if (!$date) {
            $errors .= '<p>The provided ride date was invalid.</p>';
        }

        $selectedDriver = trim((string) ($args['driver_id'] ?? ''));
        $selectedVehicle = (int) ($args['vehicle_id'] ?? 0);
        $shouldAssign = ($selectedDriver !== '' || $selectedVehicle > 0);

        if ($shouldAssign && ($selectedDriver === '' || $selectedVehicle <= 0)) {
            $errors .= '<p>Select both a driver and a vehicle to schedule this ride.</p>';
        }

        if (!$errors) {
            $startTime = $validatedTimes[0];
            $endTime = $validatedTimes[1];

            if ($shouldAssign && driver_has_time_conflict($selectedDriver, $date, $startTime, $eventID)) {
                $errors .= '<p>This driver is already scheduled for another trip within 30 minutes of this time.</p>';
            }

            if (!$errors && $shouldAssign && vehicle_has_time_conflict($selectedVehicle, $date, $startTime, $eventID)) {
                $errors .= '<p>This vehicle is already scheduled for another trip within 30 minutes of this time.</p>';
            }

            if (!$errors) {
                $eventDetails = [
                    'name' => $args['name'],
                    'date' => $date,
                    'start-time' => $startTime,
                    'end-time' => $endTime,
                    'description' => $args['description'],
                    'pickup_location' => $args['pickup_location'] ?? '',
                    'dropoff_location' => $args['dropoff_location'] ?? '',
                    'dropoff_contact' => $args['dropoff_contact'] ?? '',
                ];

                $updated = update_calendar_event_details((int) $eventID, $eventDetails);
                if (!$updated) {
                    $errors .= '<p>Could not update this ride.</p>';
                } else {
                    $wasScheduled = strtoupper(trim((string) ($event['completed'] ?? 'N'))) === 'Y'
                        || strtolower(trim((string) ($event['trip_status'] ?? ''))) === 'scheduled';
                    $assignmentChanged = trim((string) ($event['driver_id'] ?? '')) !== $selectedDriver
                        || (int) ($event['vehicle_id'] ?? 0) !== $selectedVehicle;

                    if ($shouldAssign) {
                        $assigned = assign_trip_driver_vehicle((int) $eventID, $selectedDriver, $selectedVehicle);
                        if (!$assigned) {
                            $errors .= '<p>Could not save the driver and vehicle assignment.</p>';
                        }
                    }

                    if (!$errors) {
                        

                        if ($shouldAssign && (!$wasScheduled || $assignmentChanged)) {
                            $scheduledEvent = fetch_event_by_id($eventID);
                            send_trip_scheduled_email($scheduledEvent, $selectedDriver, $selectedVehicle);
                        }

                        $status = ($shouldAssign && (!$wasScheduled || $assignmentChanged)) ? 'scheduled' : 'updated';
                        header('Location: editCalendarEvent.php?id=' . urlencode((string) $eventID) . '&status=' . $status);
                        exit;
                    }
                }
            }
        }
    }
}

$drivers = get_drivers();
$vehicles = get_vehicles();
$selectedDriver = trim((string) ($_POST['driver_id'] ?? ($event['driver_id'] ?? '')));
$selectedVehicle = (int) ($_POST['vehicle_id'] ?? ($event['vehicle_id'] ?? 0));
$calendarMonth = substr((string) ($event['startDate'] ?? date('Y-m-d')), 0, 7);
$calendarDate = (string) ($event['startDate'] ?? date('Y-m-d'));
$isRecurringChecked = isset($_POST['recurring']) || !empty($event['series_id']);
$recurrenceTypeValue = $_POST['recurrence_type'] ?? '';
$customDaysValue = $_POST['custom_days'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <?php require_once('universal.inc'); ?>
    <title>Mobility Options | Calendar Edit</title>
</head>
<body>
    <?php require_once('header.php'); ?>
    <h1>Edit Ride Details</h1>
    <main class="date">
        <?php if ($errors): ?>
            <div class="error-toast"><?php echo $errors; ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['status'])): ?>
            <div class="happy-toast"><?php echo status_text($_GET['status']); ?></div>
        <?php endif; ?>

        <h2>Ride Details</h2>
        <form id="edit-calendar-event-form" method="post">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars((string) $eventID); ?>">

            <label for="name">Rider Name</label>
            <input type="text" id="name" name="name" value="<?php echo field_value('name', 'name'); ?>" required placeholder="Enter name">

            <label for="date">Date Of Ride</label>
            <input type="date" id="date" name="date" value="<?php echo field_value('date', 'startDate'); ?>" min="<?php echo date('Y-m-d'); ?>" required>

            <label for="pickup_location">Pickup Location</label>
            <input type="text" id="pickup_location" name="pickup_location" value="<?php echo field_value('pickup_location', 'pickup_location'); ?>" placeholder="Enter location">

            <label for="dropoff_location">Drop Off Location</label>
            <input type="text" id="dropoff_location" name="dropoff_location" value="<?php echo field_value('dropoff_location', 'dropoff_location'); ?>" placeholder="Enter location">

            <label for="dropoff_contact">Drop Off Contact Information</label>
            <input type="text" id="dropoff_contact" name="dropoff_contact" value="<?php echo field_value('dropoff_contact', 'dropoff_contact'); ?>" placeholder="Enter Contact Information">

            <label for="start-time">Start Time</label>
            <input type="text" id="start-time" name="start-time" value="<?php echo isset($_POST['start-time']) ? $_POST['start-time'] : time24hto12h((string) $event['startTime']); ?>" pattern="([1-9]|10|11|12):[0-5][0-9] ?([aApP][mM])" required placeholder="Enter start time. Ex. 12:00 PM">

            <label for="end-time">End Time</label>
            <input type="text" id="end-time" name="end-time" value="<?php echo isset($_POST['end-time']) ? $_POST['end-time'] : time24hto12h((string) $event['endTime']); ?>" pattern="([1-9]|10|11|12):[0-5][0-9] ?([aApP][mM])" required placeholder="Enter end time. Ex. 12:00 PM">

            <label for="description">Description</label>
            <input type="text" id="description" name="description" value="<?php echo field_value('description', 'description'); ?>" required placeholder="Enter description">

            <label for="driver_id">Driver</label>
            <select name="driver_id" id="driver_id">
                <option value="">Leave as unassigned</option>
                <?php foreach ($drivers as $driver):
                    $driverID = trim((string) $driver['id']);
                    $driverName = $driver['first_name'] . ' ' . $driver['last_name'];
                    $selected = (strcasecmp($driverID, $selectedDriver) === 0) ? 'selected' : '';
                ?>
                    <option value="<?php echo $driverID; ?>" <?php echo $selected; ?>><?php echo $driverName; ?></option>
                <?php endforeach; ?>
            </select>

            <label for="vehicle_id">Vehicle</label>
            <select name="vehicle_id" id="vehicle_id">
                <option value="">Leave as unassigned</option>
                <?php foreach ($vehicles as $vehicle):
                    $vehicleID = (int) $vehicle['id'];
                    $vehicleLabel = $vehicle['make_model']
                        . ' [ID: ' . $vehicle['plate'] . ']'
                        . ' - Capacity: ' . $vehicle['capacity']
                        . ' - Wheelchair Accessible: '
                        . ($vehicle['wheelchair_accessible'] ? 'Yes' : 'No');
                    $selected = ($vehicleID === $selectedVehicle) ? 'selected' : '';
                ?>
                    <option value="<?php echo $vehicleID; ?>" <?php echo $selected; ?>><?php echo $vehicleLabel; ?></option>
                <?php endforeach; ?>
            </select>

           

                    <button type="submit" class="button" style="margin-top: .5rem">Save Ride Changes</button>
            <a class="button cancel" href="event.php?id=<?php echo urlencode((string) $eventID); ?>" style="margin-top: .5rem">Cancel</a>
            <a class="button" href="calendar-view_daily.php?month=<?php echo urlencode($calendarDate); ?>" style="margin-top: .5rem">Back to Calendar</a>
        </form>
    </main>
</body>
</html>