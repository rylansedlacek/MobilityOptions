
<?php
session_start(); 

date_default_timezone_set("America/New_York");


if (isset($_GET['month']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['month'])) {
    $dayStr = $_GET['month']; // string like "2025-10-18"
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
        <tr>
            <th>Sunday</th>
            <th>Monday</th>
            <th>Tuesday</th>
            <th>Wednesday</th>
            <th>Thursday</th>
            <th>Friday</th>
            <th>Saturday</th>
        </tr>
    </thead>
    <tbody>

    <?php
    $calendarStart = $dayEpoch;
        //Moving back to the last sunday.
        while (date('w', $calendarStart) > 0) {
            $calendarStart = strtotime(date('Y-m-d', $calendarStart) . ' -1 day');
        }
        $start = date('Y-m-d', $calendarStart);
        echo "<script> console.log('PHP variable start:', '\" . $start. \"');</script>";

        //Moving to the end of the week.
        $calendarEndEpoch = $calendarStart;
        while (date('w', $calendarEndEpoch) < 6) {
            $calendarEndEpoch = strtotime(date('Y-m-d', $calendarEndEpoch) . ' +1 day');
        }
        $end = date('Y-m-d', $calendarEndEpoch);
        echo "<script> console.log('PHP variable end:', '\" . $end. \"');</script>";

        require_once('database/dbEvents.php');
        $events = fetch_events_in_date_range($start, $end);
        echo "<script> console.log('Events:', " . json_encode($events) . ");</script>";
        
        echo '<tr class="calendar-week">';
        //Day work
        $date = $calendarStart;
            for ($day = 0; $day < 7; $day++) {
                    $extraAttributes = '';
                    $extraClasses = '';
                    if (date('Y-m-d', $date) == date('Y-m-d',$today)) {
                        $extraClasses = ' today';
                    }
                    if (date('m', $date) != date('m', $dayEpoch)) {
                        $extraClasses .= ' other-month';
                        $extraAttributes .= ' data-month="' . date('Y-m-d', $date) . '"';
                    }
                    $eventsStr = '';
                    $e = date('Y-m-d', $date);

                    if (isset($events[$e])) {
                        $dayEvents = $events[$e];
                        foreach ($dayEvents as $info) {
                            $completedValue = strtoupper(trim((string)($info['completed'] ?? 'N')));
                            $isScheduledRide = ($completedValue === 'Y' || $completedValue === 'YES');
                            $backgroundCol = $isScheduledRide ? '#2E7D32' : '#FBC02D';
                            if ($isScheduledRide) {
                                $targetHref = 'scheduleTrip.php?id=' . $info['id'];
                            } else {
                                $targetHref = 'event.php?id=' . $info['id'] . '&user_id=' . (isset($_SESSION['_id']) ? $_SESSION['_id'] : 'guest');
                            }
                            $eventsStr .= '<a class="calendar-event" style="background-color: ' . $backgroundCol . '" href="' . $targetHref . '">' . htmlspecialchars_decode($info['name']) . '</a>';

                        }
                    }
                    echo '<td class="calendar-day' . $extraClasses . '" ' . $extraAttributes . ' data-date="' . date('Y-m-d', $date) . '">
                        <div class="calendar-day-wrapper">
                            <p class="calendar-day-number">' . date('j', $date) . '</p>
                            ' . $eventsStr . '
                        </div>
                    </td>';
                    $date = strtotime(date('Y-m-d', $date) . ' +1 day');
                }


        echo '</tr>';?>
        </tbody>
        </table>
