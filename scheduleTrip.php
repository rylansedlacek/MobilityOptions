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
require_once('include/input-validation.php');

$eventID = $_GET['id'] ?? $_POST['id'] ?? null;
$event = fetch_event_by_id($eventID);


$errors = [];
$successMessage = '';

function val($key, $fallback = '') {
    global $event;
    return ($event[$key] ?? $fallback);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once('universal.inc'); ?>
    <title>Schedule Trip</title>
</head>
<body>
    <?php require_once('header.php'); ?>

    <main class="general">
        <h1>Schedule Trip</h1>

        <form method="POST" class="general">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($eventID); ?>">

            <section>
                <h2>Ride Request</h2>
                <p><strong>Rider:</strong> <?php echo val('name'); ?></p>
                <p><strong>Date:</strong> <?php echo val('startDate'); ?></p>
                <p><strong>Time:</strong> <?php echo val('startTime'); ?> - <?php echo val('endTime'); ?></p>
                <p><strong>Pickup:</strong> <?php echo val('pickup_location'); ?></p>
                <p><strong>Dropoff:</strong> <?php echo val('dropoff_location'); ?></p>
                <p><strong>Notes:</strong> <?php echo val('description'); ?></p>
            </section>

            <section>
                <h2>Schedule</h2>
                
            </section>

            <div style="margin-top:1rem;">
                <a class="button cancel" href="viewAllEvents.php">Back to list</a>
            </div>
        </form>

    </main>
</body>
</html>
