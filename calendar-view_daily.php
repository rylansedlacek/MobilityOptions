<?php
session_start();

date_default_timezone_set("America/New_York");

// Accept ?month=YYYY-MM-DD, fallback to today
if (isset($_GET['month']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['month'])) {
    $dayStr = $_GET['month'];
} else {
    $dayStr = date('Y-m-d'); // Default to today
}

// Get the timestamp for the day we are viewing
$dayEpoch = strtotime($dayStr);
if (!$dayEpoch) {
    header('Location: calendar.php?month=' . date("Y-m-d"));
    exit;
}

$today = strtotime(date("Y-m-d"));

// Compute previous and next week
$previousWeek = strtotime(date('Y-m-d', $dayEpoch) . ' -7 days');
$nextWeek = strtotime(date('Y-m-d', $dayEpoch) . ' +7 days');
?>

<table id="calendar"
       data-current-month="<?php echo date('Y-m-d', $dayEpoch); ?>"
       data-prev-month="<?php echo date('Y-m-d', $previousWeek); ?>"
       data-next-month="<?php echo date('Y-m-d', $nextWeek); ?>">
    <thead>
        <?php
        // Use the validated day string
        $selectedDateString = $dayStr;
        $date = $dayEpoch;

        require_once('database/dbEvents.php');
        echo "<tr><th>" . htmlspecialchars($selectedDateString) . "</th></tr>";
        ?>
    </thead>
    <tbody>
    <?php
    // Determine logged-in status for db query (avoid undefined variable)
    $loggedIn = isset($_SESSION['_id']) ? 1 : 0;

    $dayEvents = fetch_events_on_date($selectedDateString, $loggedIn);
    echo "<script>console.log('Events:', " . json_encode($dayEvents) . ");</script>";

    // Prepare cell attributes
    $extraAttributes = '';
    $extraClasses = '';
    if (date('Y-m-d', $date) == date('Y-m-d', $today)) {
        $extraClasses = ' today';
    }

    $eventsStr = '';
    if (!empty($dayEvents)) {
        foreach ($dayEvents as $info) {
            $completedValue = strtoupper(trim((string)($info['completed'] ?? 'N')));
            $isScheduledRide = ($completedValue === 'Y');
            $backgroundCol = $isScheduledRide ? '#2E7D32' : '#FBC02D';
            if ($isScheduledRide) {
                $targetHref = 'scheduleTrip.php?id=' . $info['id'];
            } else {
                $targetHref = 'event.php?id=' . $info['id'] . '&user_id=' . (isset($_SESSION['_id']) ? $_SESSION['_id'] : 'guest');
            }
            $eventsStr .= '<a class="calendar-event" style="background-color: ' . $backgroundCol . '" href="' . $targetHref . '">' . htmlspecialchars_decode($info['name']) . '</a>';
        }
    }

    // Output the single-row daily view
    echo '<tr class="calendar-week">';
    echo '<td class="calendar-day' . $extraClasses . '" ' . $extraAttributes . ' data-date="' . date('Y-m-d', $date) . '">
            <div class="calendar-day-wrapper">
                <p class="calendar-day-number">' . date('j', $date) . '</p>
                ' . $eventsStr . '
            </div>
        </td>';
    echo '</tr>';
    ?>
    </tbody>
</table>