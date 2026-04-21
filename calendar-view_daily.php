<?php
session_start();

date_default_timezone_set("America/New_York");

// get the date from the url, default to today
$dayStr = date('Y-m-d');
if (isset($_GET['month']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['month'])) {
    $dayStr = $_GET['month'];
}

$dayEpoch = strtotime($dayStr);
if (!$dayEpoch) {
    header('Location: calendar.php?month=' . date("Y-m-d"));
    exit;
}

$prevDay = date('Y-m-d', strtotime($dayStr . ' -1 day'));
$nextDay = date('Y-m-d', strtotime($dayStr . ' +1 day'));
$backMonth = date('Y-m', $dayEpoch);

require_once('database/dbEvents.php');
$loggedIn = 0;
if (isset($_SESSION['_id'])) {
    $loggedIn = 1;
}
$trips = fetch_events_on_date($dayStr, $loggedIn);
?>
<!DOCTYPE html>
<html>
<head>
    <?php require('universal.inc'); ?>
    <?php require('header.php'); ?>
    <title>Mobility Options | Daily Calendar</title>
    <style>
.daily-view-header,
.daily-trip-list,
.daily-trip-card,
.daily-trip-card-title,
.daily-trip-card-meta,
.daily-trip-card-badge,
.daily-view-nav a,
.daily-no-trips {
    font-family: Montserrat, sans-serif;
}

.daily-view-header {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem 1rem;
    margin: 0.25rem 0 1.5rem;
    border-radius: 0;
    background: transparent;
    box-shadow: none;
}

.daily-view-header h2 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 700;
    color: #111;
}

.daily-view-nav {
    display: flex;
    gap: 0.5rem;
}

.daily-view-nav a {
    text-decoration: none;
    padding: 6px 14px;
    border-radius: 6px;
    font-size: 0.88rem;
    border: 1px solid #ccc;
    color: #111;
    background: #f5f5f5;
    transition: background-color .2s;
}

.daily-view-nav a:hover {
    background: #e5e5e5;
}

.daily-view-nav-day-arrows {
    display: flex;
    gap: 2rem;
    align-items: center;
    justify-content: center;
    width: 100%;
    max-width: 600px;
    margin: 0 auto;
}

.daily-view-nav-day-arrows a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #45892e 0%, #5d9322 100%);
    color: white;
    text-decoration: none;
    font-size: 1.3rem;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 3px 10px rgba(69, 137, 46, 0.25);
    border: none;
}

.daily-view-nav-day-arrows a:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 16px rgba(69, 137, 46, 0.4);
    background: linear-gradient(135deg, #5d9322 0%, #45892e 100%);
}

.daily-trip-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding-bottom: 1.5rem;
}
.daily-trip-card {
    display: flex;
    align-items: stretch;
    border-radius: 8px;
    overflow: hidden;
    text-decoration: none;
    color: #111;
    background: #fff;
    border: 1px solid #d7d7d7;
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    transition: box-shadow .2s, transform .2s;
}

.daily-trip-card:hover {
    box-shadow: 0 7px 18px rgba(0,0,0,0.2);
    transform: translateY(-1px);
}

.daily-trip-card-accent {
    width: 7px;
    flex-shrink: 0;
}

.daily-trip-card-body {
    padding: 0.75rem 1rem;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}

.daily-trip-card-title {
    font-weight: 600;
    font-size: 1rem;
    color: #111;
}

.daily-trip-card-meta {
    color: #111;
}

.daily-trip-card-badge {
    align-self: flex-start;
    padding: 2px 9px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    color: #fff;
    margin-top: 4px;
}

.daily-no-trips {
    color: #666;
    font-style: italic;
    padding: 1rem;
    border: 1px solid #d7d7d7;
    border-radius: 8px;
    background: #fff;
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}

</style>
</head>
<body>
<main style="max-width: 800px; margin: 0 auto; padding: 0 1rem;">

<div class="daily-view-header">
    <div class="daily-view-nav-day-arrows">
        <a href="calendar-view_daily.php?month=<?php echo $prevDay; ?>">&larr;</a>
        <h2><?php echo date('l, F j, Y', $dayEpoch); ?></h2>
        <a href="calendar-view_daily.php?month=<?php echo $nextDay; ?>">&rarr;</a>
    </div>
</div>

<div class="daily-trip-list">
<?php if (empty($trips)): ?>
    <p class="daily-no-trips">No trips scheduled on this day.</p>
<?php else: ?>
    <?php foreach ($trips as $trip):
        $color = '#e6a432';
        $badge = 'Requested';
        if (strtoupper(trim($trip['completed'])) === 'Y') {
            $color = '#5d9322';
            $badge = 'Scheduled';
        }

        $name = trim($trip['name']);
        if ($name == '') { $name = 'Not entered'; }

        $pickup = trim($trip['pickup_location']);
        if ($pickup == '') { $pickup = 'Not entered'; }

        $time = 'Time not entered';
        if (trim($trip['startTime']) != '') {
            $parsed = DateTime::createFromFormat('H:i:s', trim($trip['startTime']));
            if (!$parsed) { $parsed = DateTime::createFromFormat('H:i', trim($trip['startTime'])); }
            if ($parsed) { $time = $parsed->format('g:i A'); }
        }

        $link = 'editCalendarEvent.php?id=' . $trip['id'];
    ?>
    <a class="daily-trip-card" href="<?php echo ($link); ?>">
        <div class="daily-trip-card-accent" style="background-color: <?php echo $color; ?>"></div>
        <div class="daily-trip-card-body">
            <div class="daily-trip-card-title"><?php echo ($name); ?></div>
            <div class="daily-trip-card-meta"> <?php echo ($time); ?></div>
            <div class="daily-trip-card-meta"> <?php echo ($pickup); ?></div>
            <div class="daily-trip-card-badge" style="background-color: <?php echo $color; ?>"><?php echo $badge; ?></div>
        </div>
    </a>
    <?php endforeach; ?>
<?php endif; ?>
</div>

</main>
</body>
</html>