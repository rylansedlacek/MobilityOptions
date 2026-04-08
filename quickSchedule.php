<?php session_cache_expire(30);
    session_start();
    // Make session information accessible, allowing us to associate
    // data with the logged-in user.

    ini_set("display_errors",1);
    error_reporting(E_ALL);

    $loggedIn = false;
    $accessLevel = 0;
    $userID = null;
    if (isset($_SESSION['_id'])) {
        $loggedIn = true;
        // 0 = not logged in, 1 = standard user, 2 = manager (Admin), 3 super admin (TBI)
        $accessLevel = $_SESSION['access_level'];
        $userID = $_SESSION['_id'];
    } 
    // Require admin privileges
    if ($accessLevel < 2) {
        header('Location: login.php');
        //echo 'bad access level';
        die();
    }

require_once('include/input-validation.php');
require_once('database/dbEvents.php');
require_once('database/dbPersons.php');
require_once('include/trip-workflow.php');


// A direct copy of addEvent just with the scheduleTrip Stuff added.

$errors = [];
$search_results = [];
$favorite_trips = [];
$drivers = get_drivers();
$vehicles = get_vehicles();

$formData = [
    'name' => '',
    'rider_id' => '',
    'date' => $_GET['date'] ?? '',
    'start-time' => '',
    'end-time' => '',
    'pickup-street_address' => '',
    'pickup-city' => '',
    'pickup-state' => 'VA',
    'pickup-zipcode' => '',
    'dropoff-contact' => '',
    'dropoff-street_address' => '',
    'dropoff-city' => '',
    'dropoff-state' => 'VA',
    'dropoff-zipcode' => '',
    'description' => '',
    'driver_id' => '',
    'vehicle_id' => '',
    'favorite' => '',
];

foreach ($formData as $key => $value) {
    if (isset($_GET[$key])) {
        $formData[$key] = $_GET[$key];
    }
}

if (!empty($_GET['rider_name']) && empty($formData['name'])) {
    $formData['name'] = $_GET['rider_name'];
}

if (!empty($_GET['rider_id']) && empty($formData['rider_id'])) {
    $formData['rider_id'] = $_GET['rider_id'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = array_merge($formData, sanitize($_POST, null));
    $formData['pickup-state'] = $_POST['pickup-state'] ?? $formData['pickup-state'];
    $formData['dropoff-state'] = $_POST['dropoff-state'] ?? $formData['dropoff-state'];
    $formData['favorite'] = $_POST['favorite'] ?? '';
    $formData['vehicle_id'] = $_POST['vehicle_id'] ?? '';

    $required = [
        'name',
        'date',
        'start-time',
        'end-time',
        'description',
        'pickup-street_address',
        'pickup-city',
        'pickup-state',
        'pickup-zipcode',
        'dropoff-contact',
        'dropoff-street_address',
        'dropoff-city',
        'dropoff-state',
        'dropoff-zipcode',
        'driver_id',
        'vehicle_id',
    ];

    if (!wereRequiredFieldsSubmitted($formData, $required, false)) {
        $errors[] = 'Please complete all required fields.';
    }

    $rider = null;
    if (empty($errors)) {
        if (!empty($formData['rider_id'])) {
            $rider = retrieve_person($formData['rider_id']);
        } elseif (!empty($formData['name'])) {
            $riders = retrieve_persons_by_name($formData['name']);
            if (!empty($riders)) {
                $rider = $riders[0];
                $formData['rider_id'] = $rider->get_id();
                $formData['name'] = trim($rider->get_first_name() . ' ' . $rider->get_last_name());
            }
        }

        if (!$rider) {
            $errors[] = 'Select a valid rider before scheduling the trip.';
        }
    }

    if (empty($errors) && !validateEmail($formData['dropoff-contact'])) {
        $errors[] = 'Enter a valid drop-off contact email address.';
    }

    if (empty($errors) && !validateZipcode($formData['pickup-zipcode'])) {
        $errors[] = 'Enter a valid pickup ZIP code.';
    }

    if (empty($errors) && !validateZipcode($formData['dropoff-zipcode'])) {
        $errors[] = 'Enter a valid drop-off ZIP code.';
    }

    if (empty($errors)) {
        if (validate24hTimeRange($formData['start-time'], $formData['end-time'])) {
            $startTime = $formData['start-time'];
            $endTime = $formData['end-time'];
        } else {
            $validated = validate12hTimeRangeAndConvertTo24h($formData['start-time'], $formData['end-time']);
            if (!$validated) {
                $errors[] = 'Enter a valid pickup and drop-off time range.';
            } else {
                $startTime = $validated[0];
                $endTime = $validated[1];
                $formData['start-time'] = $startTime;
                $formData['end-time'] = $endTime;
            }
        }
    }

    if (empty($errors)) {
        $tripDate = validateDate($formData['date']);
        if (!$tripDate) {
            $errors[] = 'Enter a valid trip date.';
        }
    }

    if (empty($errors)) {
        $driverID = trim((string) $formData['driver_id']);
        $vehicleID = (int) $formData['vehicle_id'];

        if ($driverID === '') {
            $errors[] = 'Please select a driver.';
        }
        if ($vehicleID <= 0) {
            $errors[] = 'Please select a vehicle.';
        }
    }

    if (empty($errors)) {
        $pickupLocation = $formData['pickup-street_address'] . ', ' .
            $formData['pickup-city'] . ', ' .
            $formData['pickup-state'] . ' ' .
            $formData['pickup-zipcode'];

        $dropoffLocation = $formData['dropoff-street_address'] . ', ' .
            $formData['dropoff-city'] . ', ' .
            $formData['dropoff-state'] . ' ' .
            $formData['dropoff-zipcode'];

        if (check_duplicate_trip_request($formData['rider_id'], $tripDate, $startTime, $pickupLocation, $dropoffLocation)) {
            $errors[] = 'This ride has already been requested.';
        }

        if (empty($errors) && driver_has_time_conflict($driverID, $tripDate, $startTime)) {
            $errors[] = 'This driver is already scheduled for another trip within 30 minutes of this time.';
        }

        if (empty($errors) && vehicle_has_time_conflict($vehicleID, $tripDate, $startTime)) {
            $errors[] = 'This vehicle is already scheduled for another trip within 30 minutes of this time.';
        }
    }

    if (empty($errors)) {
        $eventArgs = $formData;
        $eventArgs['type'] = 'Normal';
        $eventArgs['driver_id'] = null;
        $eventArgs['vehicle_id'] = null;
        $eventArgs['training_level_required'] = 'None';
        $eventArgs['startDate'] = $tripDate;
        $eventArgs['endDate'] = $tripDate;
        $eventArgs['startTime'] = $startTime;
        $eventArgs['endTime'] = $endTime;
        $eventArgs['pickup_location'] = $pickupLocation;
        $eventArgs['dropoff_location'] = $dropoffLocation;
        $eventArgs['dropoff_contact'] = $formData['dropoff-contact'];
        $eventArgs['series_id'] = bin2hex(random_bytes(16));
        $eventArgs['completed'] = 'N';

        $eventID = create_event($eventArgs);
        if (!$eventID) {
            $errors[] = 'The trip could not be created.';
        } else {
            $assigned = assign_trip_driver_vehicle($eventID, $driverID, $vehicleID);

            if (!$assigned) {
                $errors[] = 'The trip was created but could not be assigned.';
            } else {
                if (!empty($_POST['favorite']) && $_POST['favorite'] === '1') {
                    $label = !empty($eventArgs['description']) ? $eventArgs['description'] : 'Favorite Trip';
                    saveFavoriteTrip(
                        $eventArgs['rider_id'],
                        $label,
                        $eventArgs['pickup_location'],
                        $eventArgs['dropoff_location'],
                        $eventArgs['dropoff_contact'],
                        $eventArgs['description']
                    );
                }

                $scheduledEvent = fetch_event_by_id($eventID);
                if ($scheduledEvent) {
                    send_trip_scheduled_email($scheduledEvent, $driverID, $vehicleID);
                }

                header('Location: quickSchedule.php?status=scheduled&id=' . urlencode((string) $eventID));
                exit();
            }
        }
    }
}

$date = $formData['date'];
if ($date) {
    $datePattern = '/[0-9]{4}-[0-9]{2}-[0-9]{2}/';
    $timeStamp = strtotime($date);
    if (!preg_match($datePattern, $date) || !$timeStamp) {
        $date = '';
        $formData['date'] = '';
    }
}

$searchName = trim((string) ($_GET['search_name'] ?? ''));
if ($searchName !== '') {
    $search_results = find_users($searchName, '', '', '', null, null);
}

$selectedRiderId = trim((string) ($formData['rider_id'] ?? ($_GET['rider_id'] ?? '')));
if ($selectedRiderId !== '') {
    $favorite_trips = getFavoriteTripsByRiderId($selectedRiderId);
}

function field_value($key) {
    global $formData;
    return htmlspecialchars((string) ($formData[$key] ?? ''));
}

function option_selected($field, $value) {
    global $formData;
    return ((string) ($formData[$field] ?? '') === (string) $value) ? 'selected' : '';
}

?><!DOCTYPE html>
<html>
    <head>
        <?php require_once('universal.inc') ?>
        <title>Mobility Options | Quick Schedule</title>
    </head>
    <body>
        <?php require_once('header.php') ?>

        <main class="date">
            <header class="hero-header">
                <div class="center-header">
                    <h1>Quick Schedule</h1>
                </div>
            </header>

            <?php if (isset($_GET['status']) && $_GET['status'] === 'scheduled'): ?>
                <div class="happy-toast" style="margin-bottom: 1rem;">Ride request scheduled successfully.</div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <script>
                    alert("<?php echo htmlspecialchars(implode('\n', $errors), ENT_QUOTES); ?>");
                </script>
            <?php endif; ?>

            <form id="quick-schedule-form" method="POST">
                <div class="event-sect">
                    <h2 class="mt-2">Rider Search</h2>
                    <div id="rider-lookup" style="margin-bottom:12px;">
                        <input type="text" id="search_name" placeholder="Type name and click Search" value="<?php echo htmlspecialchars($searchName); ?>" style="width:100%; padding:6px;">
                        <button type="button" id="search_button" style="background:#45892e;color:#fff;border:none;cursor:pointer;">Search</button>
                    </div>
                    <?php if (!empty($search_results)): ?>
                        <h3 class="mt-2">Search Results</h3>
                        <ul style="list-style:none; padding:0; margin-bottom:12px; max-height:150px; overflow:auto; border:2px solid #45892e; border-radius:4px;">
                            <?php foreach ($search_results as $rider): ?>
                                <li style="padding:6px; border-bottom:1px solid #eee; cursor:pointer;" onclick="selectRider('<?php echo htmlspecialchars($rider->get_first_name() . ' ' . $rider->get_last_name(), ENT_QUOTES); ?>','<?php echo htmlspecialchars($rider->get_id(), ENT_QUOTES); ?>')">
                                    <?php echo htmlspecialchars($rider->get_first_name() . ' ' . $rider->get_last_name() . ' (' . $rider->get_id() . ')'); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <?php if (!empty($favorite_trips)): ?>
                        <h3 class="mt-2">Favorite Trips</h3>
                        <ul style="list-style:none; padding:0; margin-bottom:12px; max-height:200px; overflow:auto; border:2px solid #45892e; border-radius:4px;">
                            <?php foreach ($favorite_trips as $fav): ?>
                                <li onclick="applyFavorite(<?php echo htmlspecialchars(json_encode($fav), ENT_QUOTES); ?>)"
                                    style="padding:8px; border-bottom:1px solid #eee; cursor:pointer;"
                                    onmouseover="this.style.background='#f0f8ed'"
                                    onmouseout="this.style.background=''">
                                    <strong><?php echo htmlspecialchars($fav['label']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($fav['pickup_location']); ?> --> <?php echo htmlspecialchars($fav['dropoff_location']); ?></small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

                <div class="event-sect">
                    <h2 class="mt-2">Rider Information</h2>
                    <label for="name">* Rider Name </label>
                    <input type="text" id="name" name="name" required placeholder="Enter name" value="<?php echo field_value('name'); ?>">
                    <input type="hidden" id="rider_id" name="rider_id" value="<?php echo field_value('rider_id'); ?>">
                </div>

                <div class="event-sect">
                    <h2 class="mt-2">Pickup Information</h2>
                    <div class="event-datetime">
                        <div class="event-date">
                            <label for="date">* Pickup Date </label>
                            <input type="date" id="date" name="date" value="<?php echo field_value('date'); ?>" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                        <div class="event-date">
                            <label for="start-time">* Start Time </label>
                            <input type="time" id="start-time" name="start-time" value="<?php echo field_value('start-time'); ?>" required>
                        </div>
                    </div>

                    <label for="pickup-street_address"><em>* </em>Street Address</label>
                    <input type="text" id="pickup-street_address" name="pickup-street_address" required placeholder="Enter street address" value="<?php echo field_value('pickup-street_address'); ?>">

                    <label for="pickup-city"><em>* </em>City</label>
                    <input type="text" id="pickup-city" name="pickup-city" required placeholder="Enter city" value="<?php echo field_value('pickup-city'); ?>">

                    <label for="pickup-state"><em>* </em>State</label>
                    <select id="pickup-state" name="pickup-state" required>
                        <option value="AL" <?php echo option_selected('pickup-state', 'AL'); ?>>Alabama</option>
                        <option value="AK" <?php echo option_selected('pickup-state', 'AK'); ?>>Alaska</option>
                        <option value="AZ" <?php echo option_selected('pickup-state', 'AZ'); ?>>Arizona</option>
                        <option value="AR" <?php echo option_selected('pickup-state', 'AR'); ?>>Arkansas</option>
                        <option value="CA" <?php echo option_selected('pickup-state', 'CA'); ?>>California</option>
                        <option value="CO" <?php echo option_selected('pickup-state', 'CO'); ?>>Colorado</option>
                        <option value="CT" <?php echo option_selected('pickup-state', 'CT'); ?>>Connecticut</option>
                        <option value="DE" <?php echo option_selected('pickup-state', 'DE'); ?>>Delaware</option>
                        <option value="DC" <?php echo option_selected('pickup-state', 'DC'); ?>>District Of Columbia</option>
                        <option value="FL" <?php echo option_selected('pickup-state', 'FL'); ?>>Florida</option>
                        <option value="GA" <?php echo option_selected('pickup-state', 'GA'); ?>>Georgia</option>
                        <option value="HI" <?php echo option_selected('pickup-state', 'HI'); ?>>Hawaii</option>
                        <option value="ID" <?php echo option_selected('pickup-state', 'ID'); ?>>Idaho</option>
                        <option value="IL" <?php echo option_selected('pickup-state', 'IL'); ?>>Illinois</option>
                        <option value="IN" <?php echo option_selected('pickup-state', 'IN'); ?>>Indiana</option>
                        <option value="IA" <?php echo option_selected('pickup-state', 'IA'); ?>>Iowa</option>
                        <option value="KS" <?php echo option_selected('pickup-state', 'KS'); ?>>Kansas</option>
                        <option value="KY" <?php echo option_selected('pickup-state', 'KY'); ?>>Kentucky</option>
                        <option value="LA" <?php echo option_selected('pickup-state', 'LA'); ?>>Louisiana</option>
                        <option value="ME" <?php echo option_selected('pickup-state', 'ME'); ?>>Maine</option>
                        <option value="MD" <?php echo option_selected('pickup-state', 'MD'); ?>>Maryland</option>
                        <option value="MA" <?php echo option_selected('pickup-state', 'MA'); ?>>Massachusetts</option>
                        <option value="MI" <?php echo option_selected('pickup-state', 'MI'); ?>>Michigan</option>
                        <option value="MN" <?php echo option_selected('pickup-state', 'MN'); ?>>Minnesota</option>
                        <option value="MS" <?php echo option_selected('pickup-state', 'MS'); ?>>Mississippi</option>
                        <option value="MO" <?php echo option_selected('pickup-state', 'MO'); ?>>Missouri</option>
                        <option value="MT" <?php echo option_selected('pickup-state', 'MT'); ?>>Montana</option>
                        <option value="NE" <?php echo option_selected('pickup-state', 'NE'); ?>>Nebraska</option>
                        <option value="NV" <?php echo option_selected('pickup-state', 'NV'); ?>>Nevada</option>
                        <option value="NH" <?php echo option_selected('pickup-state', 'NH'); ?>>New Hampshire</option>
                        <option value="NJ" <?php echo option_selected('pickup-state', 'NJ'); ?>>New Jersey</option>
                        <option value="NM" <?php echo option_selected('pickup-state', 'NM'); ?>>New Mexico</option>
                        <option value="NY" <?php echo option_selected('pickup-state', 'NY'); ?>>New York</option>
                        <option value="NC" <?php echo option_selected('pickup-state', 'NC'); ?>>North Carolina</option>
                        <option value="ND" <?php echo option_selected('pickup-state', 'ND'); ?>>North Dakota</option>
                        <option value="OH" <?php echo option_selected('pickup-state', 'OH'); ?>>Ohio</option>
                        <option value="OK" <?php echo option_selected('pickup-state', 'OK'); ?>>Oklahoma</option>
                        <option value="OR" <?php echo option_selected('pickup-state', 'OR'); ?>>Oregon</option>
                        <option value="PA" <?php echo option_selected('pickup-state', 'PA'); ?>>Pennsylvania</option>
                        <option value="RI" <?php echo option_selected('pickup-state', 'RI'); ?>>Rhode Island</option>
                        <option value="SC" <?php echo option_selected('pickup-state', 'SC'); ?>>South Carolina</option>
                        <option value="SD" <?php echo option_selected('pickup-state', 'SD'); ?>>South Dakota</option>
                        <option value="TN" <?php echo option_selected('pickup-state', 'TN'); ?>>Tennessee</option>
                        <option value="TX" <?php echo option_selected('pickup-state', 'TX'); ?>>Texas</option>
                        <option value="UT" <?php echo option_selected('pickup-state', 'UT'); ?>>Utah</option>
                        <option value="VT" <?php echo option_selected('pickup-state', 'VT'); ?>>Vermont</option>
                        <option value="VA" <?php echo option_selected('pickup-state', 'VA'); ?>>Virginia</option>
                        <option value="WA" <?php echo option_selected('pickup-state', 'WA'); ?>>Washington</option>
                        <option value="WV" <?php echo option_selected('pickup-state', 'WV'); ?>>West Virginia</option>
                        <option value="WI" <?php echo option_selected('pickup-state', 'WI'); ?>>Wisconsin</option>
                        <option value="WY" <?php echo option_selected('pickup-state', 'WY'); ?>>Wyoming</option>
                    </select>

                    <label for="pickup-zipcode"><em>* </em>Zip Code</label>
                    <input type="text" id="pickup-zipcode" name="pickup-zipcode" pattern="^\d{5}(-\d{4})?$" required placeholder="Ex: 12345 or 12345-6789" value="<?php echo field_value('pickup-zipcode'); ?>">
                </div>

                <div class="event-sect">
                    <h2 class="mt-2">Drop-Off Information</h2>
                    <div class="event-datetime">
                        <div class="event-date">
                            <label for="end-time">* End Time </label>
                            <input type="time" id="end-time" name="end-time" value="<?php echo field_value('end-time'); ?>" required>
                        </div>
                    </div>

                    <label for="dropoff-contact">* Drop Off Contact Information</label>
                    <input type="email" id="dropoff-contact" name="dropoff-contact" required value="<?php echo field_value('dropoff-contact'); ?>">

                    <label for="dropoff-street_address"><em>* </em>Street Address</label>
                    <input type="text" id="dropoff-street_address" name="dropoff-street_address" required placeholder="Enter street address" value="<?php echo field_value('dropoff-street_address'); ?>">

                    <label for="dropoff-city"><em>* </em>City</label>
                    <input type="text" id="dropoff-city" name="dropoff-city" required placeholder="Enter city" value="<?php echo field_value('dropoff-city'); ?>">

                    <label for="dropoff-state"><em>* </em>State</label>
                    <select id="dropoff-state" name="dropoff-state" required>
                        <option value="AL" <?php echo option_selected('dropoff-state', 'AL'); ?>>Alabama</option>
                        <option value="AK" <?php echo option_selected('dropoff-state', 'AK'); ?>>Alaska</option>
                        <option value="AZ" <?php echo option_selected('dropoff-state', 'AZ'); ?>>Arizona</option>
                        <option value="AR" <?php echo option_selected('dropoff-state', 'AR'); ?>>Arkansas</option>
                        <option value="CA" <?php echo option_selected('dropoff-state', 'CA'); ?>>California</option>
                        <option value="CO" <?php echo option_selected('dropoff-state', 'CO'); ?>>Colorado</option>
                        <option value="CT" <?php echo option_selected('dropoff-state', 'CT'); ?>>Connecticut</option>
                        <option value="DE" <?php echo option_selected('dropoff-state', 'DE'); ?>>Delaware</option>
                        <option value="DC" <?php echo option_selected('dropoff-state', 'DC'); ?>>District Of Columbia</option>
                        <option value="FL" <?php echo option_selected('dropoff-state', 'FL'); ?>>Florida</option>
                        <option value="GA" <?php echo option_selected('dropoff-state', 'GA'); ?>>Georgia</option>
                        <option value="HI" <?php echo option_selected('dropoff-state', 'HI'); ?>>Hawaii</option>
                        <option value="ID" <?php echo option_selected('dropoff-state', 'ID'); ?>>Idaho</option>
                        <option value="IL" <?php echo option_selected('dropoff-state', 'IL'); ?>>Illinois</option>
                        <option value="IN" <?php echo option_selected('dropoff-state', 'IN'); ?>>Indiana</option>
                        <option value="IA" <?php echo option_selected('dropoff-state', 'IA'); ?>>Iowa</option>
                        <option value="KS" <?php echo option_selected('dropoff-state', 'KS'); ?>>Kansas</option>
                        <option value="KY" <?php echo option_selected('dropoff-state', 'KY'); ?>>Kentucky</option>
                        <option value="LA" <?php echo option_selected('dropoff-state', 'LA'); ?>>Louisiana</option>
                        <option value="ME" <?php echo option_selected('dropoff-state', 'ME'); ?>>Maine</option>
                        <option value="MD" <?php echo option_selected('dropoff-state', 'MD'); ?>>Maryland</option>
                        <option value="MA" <?php echo option_selected('dropoff-state', 'MA'); ?>>Massachusetts</option>
                        <option value="MI" <?php echo option_selected('dropoff-state', 'MI'); ?>>Michigan</option>
                        <option value="MN" <?php echo option_selected('dropoff-state', 'MN'); ?>>Minnesota</option>
                        <option value="MS" <?php echo option_selected('dropoff-state', 'MS'); ?>>Mississippi</option>
                        <option value="MO" <?php echo option_selected('dropoff-state', 'MO'); ?>>Missouri</option>
                        <option value="MT" <?php echo option_selected('dropoff-state', 'MT'); ?>>Montana</option>
                        <option value="NE" <?php echo option_selected('dropoff-state', 'NE'); ?>>Nebraska</option>
                        <option value="NV" <?php echo option_selected('dropoff-state', 'NV'); ?>>Nevada</option>
                        <option value="NH" <?php echo option_selected('dropoff-state', 'NH'); ?>>New Hampshire</option>
                        <option value="NJ" <?php echo option_selected('dropoff-state', 'NJ'); ?>>New Jersey</option>
                        <option value="NM" <?php echo option_selected('dropoff-state', 'NM'); ?>>New Mexico</option>
                        <option value="NY" <?php echo option_selected('dropoff-state', 'NY'); ?>>New York</option>
                        <option value="NC" <?php echo option_selected('dropoff-state', 'NC'); ?>>North Carolina</option>
                        <option value="ND" <?php echo option_selected('dropoff-state', 'ND'); ?>>North Dakota</option>
                        <option value="OH" <?php echo option_selected('dropoff-state', 'OH'); ?>>Ohio</option>
                        <option value="OK" <?php echo option_selected('dropoff-state', 'OK'); ?>>Oklahoma</option>
                        <option value="OR" <?php echo option_selected('dropoff-state', 'OR'); ?>>Oregon</option>
                        <option value="PA" <?php echo option_selected('dropoff-state', 'PA'); ?>>Pennsylvania</option>
                        <option value="RI" <?php echo option_selected('dropoff-state', 'RI'); ?>>Rhode Island</option>
                        <option value="SC" <?php echo option_selected('dropoff-state', 'SC'); ?>>South Carolina</option>
                        <option value="SD" <?php echo option_selected('dropoff-state', 'SD'); ?>>South Dakota</option>
                        <option value="TN" <?php echo option_selected('dropoff-state', 'TN'); ?>>Tennessee</option>
                        <option value="TX" <?php echo option_selected('dropoff-state', 'TX'); ?>>Texas</option>
                        <option value="UT" <?php echo option_selected('dropoff-state', 'UT'); ?>>Utah</option>
                        <option value="VT" <?php echo option_selected('dropoff-state', 'VT'); ?>>Vermont</option>
                        <option value="VA" <?php echo option_selected('dropoff-state', 'VA'); ?>>Virginia</option>
                        <option value="WA" <?php echo option_selected('dropoff-state', 'WA'); ?>>Washington</option>
                        <option value="WV" <?php echo option_selected('dropoff-state', 'WV'); ?>>West Virginia</option>
                        <option value="WI" <?php echo option_selected('dropoff-state', 'WI'); ?>>Wisconsin</option>
                        <option value="WY" <?php echo option_selected('dropoff-state', 'WY'); ?>>Wyoming</option>
                    </select>

                    <label for="dropoff-zipcode"><em>* </em>Zip Code</label>
                    <input type="text" id="dropoff-zipcode" name="dropoff-zipcode" pattern="^\d{5}(-\d{4})?$" required placeholder="Ex: 12345 or 12345-6789" value="<?php echo field_value('dropoff-zipcode'); ?>">
                </div>

                <div class="event-sect">
                    <label for="description">* Description </label>
                    <input type="text" id="description" name="description" required placeholder="Enter description (e.g. 'Ride to VA hospital')" value="<?php echo field_value('description'); ?>">
                </div>

                <div class="event-sect">
                    <h2 class="mt-2">Assign Driver &amp; Vehicle</h2>
                    <label for="driver_id">* Driver</label>
                    <select name="driver_id" id="driver_id" required>
                        <option value="">Select a driver</option>
                        <?php foreach ($drivers as $driver): ?>
                            <?php $driverID = trim((string) $driver['id']); ?>
                            <option value="<?php echo htmlspecialchars($driverID); ?>" <?php echo option_selected('driver_id', $driverID); ?>>
                                <?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="vehicle_id">* Vehicle</label>
                    <select name="vehicle_id" id="vehicle_id" required>
                        <option value="">Select a vehicle</option>
                        <?php foreach ($vehicles as $vehicle): ?>
                            <?php
                                $vehicleID = (int) $vehicle['id'];
                                $vehicleLabel = $vehicle['make_model'] .
                                    ' [ID: ' . $vehicle['plate'] . ']' .
                                    ' - Capacity: ' . $vehicle['capacity'] .
                                    ' - Wheelchair Accessible: ' .
                                    ($vehicle['wheelchair_accessible'] ? 'Yes' : 'No');
                            ?>
                            <option value="<?php echo $vehicleID; ?>" <?php echo option_selected('vehicle_id', (string) $vehicleID); ?>>
                                <?php echo htmlspecialchars($vehicleLabel); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($selectedRiderId !== ''): ?>
                    <fieldset style="display:flex; align-items:center; gap:8px; margin-bottom:8px;" id="favorite-fieldset">
                        <legend>Save as Favorite Trip</legend>
                        <label style="margin-top:12px; padding:12px; border:1px solid #e0e0e0; border-radius:8px;">
                            <input type="checkbox" id="favorite" name="favorite" value="1" <?php echo !empty($formData['favorite']) ? 'checked' : ''; ?>>
                            Save this trip as a favorite
                        </label>
                    </fieldset>
                <?php endif; ?>

                <input type="submit" value="Request &amp; Schedule Ride" style="width:100%;">
            </form>

            <script>
                document.getElementById('search_button').addEventListener('click', function() {
                    const searchValue = document.getElementById('search_name').value.trim();
                    if (searchValue.length > 0) {
                        window.location = 'quickSchedule.php?search_name=' + encodeURIComponent(searchValue);
                    }
                });

                function selectRider(name, id) {
                    document.getElementById('name').value = name;
                    document.getElementById('rider_id').value = id;
                    const url = new URL(window.location.href);
                    url.searchParams.set('rider_id', id);
                    url.searchParams.set('rider_name', name);
                    url.searchParams.set('name', name);
                    url.searchParams.set('search_name', document.getElementById('search_name').value);
                    window.location = url.toString();
                }

                function applyFavorite(fav) {
                    function parseAddress(full) {
                        if (!full) return { street: '', city: '', state: 'VA', zip: '' };
                        const parts = full.split(',');
                        const street = parts[0] || '';
                        const city = (parts[1] || '').trim();
                        const stateZip = (parts[2] || '').trim().split(' ');
                        const state = stateZip[0] || 'VA';
                        const zip = stateZip.slice(1).join(' ') || '';
                        return { street, city, state, zip };
                    }

                    const pickup = parseAddress(fav.pickup_location);
                    const dropoff = parseAddress(fav.dropoff_location);

                    document.getElementById('pickup-street_address').value = pickup.street;
                    document.getElementById('pickup-city').value = pickup.city;
                    document.getElementById('pickup-state').value = pickup.state;
                    document.getElementById('pickup-zipcode').value = pickup.zip;
                    document.getElementById('dropoff-street_address').value = dropoff.street;
                    document.getElementById('dropoff-city').value = dropoff.city;
                    document.getElementById('dropoff-state').value = dropoff.state;
                    document.getElementById('dropoff-zipcode').value = dropoff.zip;

                    if (fav.dropoff_contact) {
                        document.getElementById('dropoff-contact').value = fav.dropoff_contact;
                    }
                    if (fav.description) {
                        document.getElementById('description').value = fav.description;
                    }

                    document.getElementById('date').scrollIntoView({ behavior: 'smooth' });
                    checkShowFavorite();
                }

                function checkShowFavorite() {
                    const fieldset = document.getElementById('favorite-fieldset');
                    if (!fieldset) return;
                    const pickupFilled = document.getElementById('pickup-street_address').value.trim() !== '';
                    const dropoffFilled = document.getElementById('dropoff-street_address').value.trim() !== '';
                    fieldset.style.display = (pickupFilled && dropoffFilled) ? 'flex' : 'none';
                }

                const pickupAddress = document.getElementById('pickup-street_address');
                const dropoffAddress = document.getElementById('dropoff-street_address');
                if (pickupAddress && dropoffAddress) {
                    pickupAddress.addEventListener('input', checkShowFavorite);
                    dropoffAddress.addEventListener('input', checkShowFavorite);
                    checkShowFavorite();
                }
            </script>

            <br/>
            <br/>
            <center><a class="button cancel" href="eventManagement.php">Return to Ride Management</a></center>
        </main>
    </body>
</html>