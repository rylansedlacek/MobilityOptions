<?php

date_default_timezone_set("America/New_York");

// Accept a ?month=YYYY-MM query param fallback to current month
if (isset($_GET['month']) && preg_match('/^\d{4}-\d{2}$/', $_GET['month'])) {
    $monthStr = $_GET['month'];           // string like "2025-10"
} else {
    $monthStr = date('Y-m');
}

// Extract pieces from the string
$year = substr($monthStr, 0, 4);
$month2digit = substr($monthStr, 5, 2);  // "01", "02" ... used when comparing date('m', ...)

// canonical epoch/timestamps for the month and first day
$today = strtotime(date("Y-m-d"));
$firstOfMonthStr = $monthStr . '-01';
$firstOfMonthEpoch = strtotime($firstOfMonthStr);
$monthEpoch = strtotime($monthStr . '-01'); // same as firstOfMonthEpoch; kept for clarity

// Defensive: if invalid month param redirect to calendar.php with current month
if (!$monthEpoch) {
    header('Location: calendar.php?month=' . date("Y-m"));
    exit;
}

// compute previous and next month (epochs)
$previousMonth = strtotime(date('Y-m', $monthEpoch) . ' -1 month');
$nextMonth = strtotime(date('Y-m', $monthEpoch) . ' +1 month');

// Set calendar start to first of month, then back up to the Sunday that should be the first cell
$calendarStart = $firstOfMonthEpoch;
while (date('w', $calendarStart) > 0) { // date('w') returns 0 for Sunday
    $calendarStart = strtotime(date('Y-m-d', $calendarStart) . ' -1 day');
}

// Start with 5 weeks (35 days) and extend if needed
$calendarEnd = date('Y-m-d', strtotime(date('Y-m-d', $calendarStart) . ' +34 day'));
$calendarEndEpoch = strtotime($calendarEnd);
$weeks = 5;
if (date('m', strtotime($calendarEnd . ' +1 day')) != $monthEpoch) {
    // Need another row (6 weeks) to show all days of the month
    $weeks = 6;
    $calendarEnd = date('Y-m-d', strtotime(date('Y-m-d', $calendarStart) . ' +41 day'));
    $calendarEndEpoch = strtotime($calendarEnd);
}


function time_label($eventInfo, $isScheduledRide) {
    $rawStartTime = trim((string)($eventInfo['startTime'] ?? ''));
    $displayTime = 'Time not entered';
    if ($rawStartTime !== '') {
        $parsedTime = DateTime::createFromFormat('H:i:s', $rawStartTime);
        if (!($parsedTime instanceof DateTime)) {
            $parsedTime = DateTime::createFromFormat('H:i', $rawStartTime);
        }
        
        $displayTime = null;
        if ($parsedTime instanceof DateTime) {
             $displayTime = $parsedTime->format('g:i A');
        } else {
            $displayTime = $rawStartTime;
        }
    }

    $prefix = null;
    if ($isScheduledRide ) {
        $prefix = 'Scheduled: ';
    } else {
         $prefix ='Requested: ';
    }

    return $prefix . $displayTime;
}

// add Modal css here
?>
 
<style>
.trip-popup {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: #11111124;
}

.trip-popup:target {
    display: block;
}

.trip-popup-card {
    width: 92%;
    max-width: 430px;
    margin: 90px auto;
    background: #ffffff;
    border-radius: 10px;
    padding: 18px;
    box-shadow: 0 8px 22px #11111124;
}

.trip-popup-card h3 {
    margin: 0 0 12px 0;
    font-size: 19px;
}


.trip-popup-actions {
    margin-top: 16px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.trip-popup-btn {
    text-decoration: none;
    border: 1px solid #888;
    border-radius: 7px;
    padding: 9px 12px;
    color: #111;
    background: #f6f6f6;
    font-size: 14px;
}

.trip-popup-btn-primary {
    background: #2E7D32;
    border-color: #2E7D32;
    color: #fff;
}
.trip-popup-btn-close {
    background: #d44b44;
    border-color: #d44b44;
    color: #fff;
}
</style>

                <!-- Add navigation data to the calendar -->
                <table id="calendar" 
                       data-current-month="<?php echo date('Y-m', $monthEpoch); ?>"
                       data-prev-month="<?php echo date('Y-m', $previousMonth); ?>"
                       data-next-month="<?php echo date('Y-m', $nextMonth); ?>">
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
                        $date = $calendarStart;
                        $start = date('Y-m-d', $calendarStart);
                        $end = date('Y-m-d', $calendarEndEpoch);
                        require_once('database/dbEvents.php');
                        $loggedIn = 0; //Logged in set to 0 change later
                        $events = fetch_events_in_date_range($start, $end);
                        for ($week = 0; $week < $weeks; $week++) {
                            echo '
                                <tr class="calendar-week">
                            ';
                            for ($day = 0; $day < 7; $day++) {
                                $extraAttributes = '';
                                $extraClasses = '';
                                if ($date == $today) {
                                    $extraClasses = ' today';
                                }
                                if (date('m', $date) != date('m', $monthEpoch)) {
                                    $extraClasses .= ' other-month';
                                    $extraAttributes .= ' data-month="' . date('Y-m', $date) . '"';
                                }
                                $eventsStr = '';
                                $e = date('Y-m-d', $date);

                                if (isset($events[$e])) {
                                    $dayEvents = $events[$e];
                                    foreach ($dayEvents as $info) {
                                        $completedValue = strtoupper(trim((string)($info['completed'] ?? 'N')));
                                        $isScheduledRide = ($completedValue === 'Y');
                                        $eventLabel = time_label($info, $isScheduledRide);
                                        $backgroundCol = $isScheduledRide ? '#2E7D32' : '#FBC02D';
                                        $popupId = 'trip-popup-' . $info['id'] . '-' . str_replace('-', '', $e); // this makes the url
                                        $rideSchedulerHref = 'scheduleTrip.php?id=' . $info['id']; // so its prepopulated

                                        $riderName = trim((string)($info['name'] ?? ''));
                                        if ($riderName === '') { $riderName = 'Not entered'; }

                                        $rawStartTime = trim((string)($info['startTime'] ?? ''));
                                        $popupParsedTime = DateTime::createFromFormat('H:i', $rawStartTime);
                                        if($popupParsedTime == false) {
                                            $popupTime = "N/A";
                                        } else {
                                        if($popupParsedTime == false) {
                                            $popupTime = "N/A";
                                        } else {
                                            $popupTime = $popupParsedTime->format('g:i A');
                                        }                                        }

                                        $rawDate = trim((string)($info['startDate'] ?? '')); // get date and format
                                        $popupDateEpoch = strtotime($rawDate);
                                        $popupDate = date('m/d/Y', $popupDateEpoch);
                                       
                                        $pickupLocation = trim((string)($info['pickup_location'] ?? ''));

                                       // this was so silly to do this way - but I like it
                                        $eventsStr .= '<a class="calendar-event" style="background-color: ' . $backgroundCol . '" href="#' .$popupId . '">' . $eventLabel . '</a>';
                                        $eventsStr .= '<div id="' . $popupId . '" class="trip-popup">';
                                        $eventsStr .= '<div class="trip-popup-card">';
                                        $eventsStr .= '<h3>Trip Details</h3>';
                                        $eventsStr .= '<div class="trip-popup-row"><strong>Rider:</strong> ' . $riderName . '</div>';
                                        $eventsStr .= '<div class="trip-popup-row"><strong>Time:</strong> ' . $popupTime . '</div>';
                                        $eventsStr .= '<div class="trip-popup-row"><strong>Date:</strong> ' . $popupDate . '</div>';
                                        $eventsStr .= '<div class="trip-popup-row"><strong>Pickup:</strong> ' . $pickupLocation . '</div>';
                                      
                                        $eventsStr .= '<div class="trip-popup-actions">';
                                        $eventsStr .= '<a class="trip-popup-btn trip-popup-btn-primary" href="' . $rideSchedulerHref . '">Go To Scheduler</a>';
                                        $eventsStr .= '<a class="trip-popup-btn trip-popup-btn-close" href="#">Close</a>';
                                        $eventsStr .= '</div>';
                                        $eventsStr .= '</div>';
                                        $eventsStr .= '</div>';
                                        
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
                            echo '
                                </tr>';
                        }
                    ?>
                    </tbody>
                </table>
</html>