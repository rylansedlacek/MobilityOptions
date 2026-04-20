<?php
//for bug testing
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/sendEmail.php';

define('IMAP_HOST',  '{imap.gmail.com:993/imap/ssl/novalidate-cert}INBOX');
define('IMAP_USER',  'mobilityoptions.notifications@gmail.com');
define('IMAP_PASS',  'pbhg xxgo ejop ycgt');

define('DB_HOST', 'localhost');
define('DB_NAME', 'mobilitydb');
define('DB_USER', 'mobilitydb');
define('DB_PASS', 'mobilitydb');

function getDb(): PDO
{
    static $pdo = null;
    if (!$pdo) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8',
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            logIt('Database connection failed: ' . $e->getMessage());
            throw new RuntimeException('Database connection failed. Check DB_USER/DB_PASS in email/checkInbox.php.');
        }
    }
    return $pdo;
}
//error catching and logging
function logIt(string $msg): void
{
    file_put_contents(
        __DIR__ . '/inbox_errors.log',
        '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL,
        FILE_APPEND
    );
}

function doTripStatus($tripStatus, $completed)
{
    $status = strtolower((string)$tripStatus);
    $completedValue = strtoupper((string)$completed);
    if ($status === 'in_progress') {
        return 'In Progress';
    }
    if ($status === 'scheduled') {
        return 'Scheduled';
    }
    if ($status === 'completed') {
        return 'Completed';
    }
    if ($status === 'cancelled' || $status === 'canceled') {
        return 'Cancelled';
    }
    if ($status === 'requested') {
        return 'Requested';
    }
    if ($completedValue === 'N') {
        return 'Requested';
    }
    if ($completedValue === 'Y') {
        return 'Scheduled';
    }

    return 'Not Scheduled';
}

// connect to Gmail via IMAP 
$inbox = imap_open(IMAP_HOST, IMAP_USER, IMAP_PASS);
if (!$inbox) {
    die('IMAP connect failed: ' . imap_last_error());
}

echo "IMAP connected successfully.<br>";

$emails = imap_search($inbox, 'UNSEEN');

if (!$emails) {
    echo "No unread emails found.";
    imap_close($inbox);
    exit;
}

echo "Found " . count($emails) . " unread email(s).<br><br>";

foreach ($emails as $msgNum) {
    $header    = imap_headerinfo($inbox, $msgNum);
    $subject   = isset($header->subject) ? imap_utf8($header->subject) : '';
    $fromEmail = strtolower(trim(
        $header->from[0]->mailbox . '@' . $header->from[0]->host
    ));

    echo "Processing email from: {$fromEmail} | Subject: {$subject}<br>";

    imap_setflag_full($inbox, (string)$msgNum, '\\Seen');


    //fetch body, this handles plain text, HTML, and Outlook formatted emails
    $body = '';
    $rawBody = imap_fetchbody($inbox, $msgNum, '1');

    $decoded = base64_decode($rawBody, true);
    if ($decoded !== false && mb_detect_encoding($decoded, 'UTF-8', true)) {
        $body = $decoded;
    } else {
        $decoded = quoted_printable_decode($rawBody);
        if (!empty($decoded)) {
            $body = $decoded;
        } else {
            $body = $rawBody;
        }
    }

    if (empty(trim($body))) {
        $body = imap_fetchbody($inbox, $msgNum, '');
    }

    $body = strip_tags($body);

    if (
        stripos($subject, 'status') === false &&
        stripos($body, 'status') === false &&
        stripos($subject, 'stop') === false &&
        stripos($body, 'stop') === false &&
        stripos($subject, 'start') === false &&
        stripos($body, 'start') === false
    ) {
        echo "→ Skipped (no status/start/stop keyword in subject or body)<br><br>";
        continue;
    }
    if (
        stripos($subject, 'status') === true &&
        stripos($body, 'status') === true
    ) {
        $pdo  = getDb();
        $stmt = $pdo->prepare("
        SELECT
            p.first_name,
            p.last_name,
            e.startDate,
            e.startTime,
            e.endTime,
            e.pickup_location,
            e.dropoff_location,
            e.trip_status,
            e.completed
        FROM dbpersons p
        JOIN dbevents e ON e.rider_id = p.id
        WHERE LOWER(p.email) = :email
           AND (e.trip_status NOT IN ('completed', 'cancelled') OR e.trip_status IS NULL)
        ORDER BY e.startDate ASC, e.startTime ASC
        LIMIT 1
    ");
        $stmt->execute([':email' => $fromEmail]);
        $ride = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ride) {
            echo "→ No upcoming ride found for {$fromEmail}<br>";
            sendEmailsDirect(
                [$fromEmail],
                'Mobility Options',
                'Your Ride Status',
                "Hello,\n\n" .
                    "We could not find an upcoming ride associated with this email address.\n\n" .
                    "If you believe this is an error, please contact us directly.\n\n" .
                    "Thank you,\n" .
                    "Healthy Generations - Mobility Options"
            );
            logIt("No ride found for: {$fromEmail}");
            continue;
        }

        //build the reply
        $name      = trim($ride['first_name'] . ' ' . $ride['last_name']);
        $date      = $ride['startDate'];
        $startTime = date('g:i A', strtotime($ride['startTime']));
        $endTime   = date('g:i A', strtotime($ride['endTime']));
        $pickup    = $ride['pickup_location'];
        $dropoff   = $ride['dropoff_location'];
        $status    = doTripStatus($ride['trip_status'], $ride['completed']);
        //? ucfirst(str_replace('_', ' ', $ride['trip_status'])) : 'Unknown';
        //ucfirst(str_replace('_', ' ', $ride['trip_status']));

        $body =
            "Hello {$name},\n\n" .
            "Here is your most current upcoming ride:\n\n" .
            "Status: {$status}\n" .
            "Date: {$date}\n" .
            "Time: {$startTime} - {$endTime}\n" .
            "Pickup: {$pickup}\n" .
            "Dropoff: {$dropoff}\n\n" .
            "If you have any questions, please contact us directly.\n\n" .
            "Thank you,\n" .
            "Healthy Generations - Mobility Options";

        $result = sendEmailsDirect(
            [$fromEmail],
            'Mobility Options',
            'Your Ride Status',
            $body
        );

        if ($result['success']) {
            echo "→ Status reply sent to: {$fromEmail}<br>";
            logIt("Status reply sent to: {$fromEmail}");
        } else {
            echo "→ Failed to send reply to: {$fromEmail}<br>";
            logIt("Failed reply to {$fromEmail}: " . json_encode($result['failed']));
        }

        echo "<br>";
    }

    else if (
        stripos($subject, 'stop') !== false ||
        stripos($body, 'stop') !== false
    ) {
        $pdo = getDb();

        $stmt = $pdo->prepare("
        SELECT id, first_name, last_name
        FROM dbpersons
        WHERE LOWER(email) = :email
        LIMIT 1
    ");
        $stmt->execute([':email' => $fromEmail]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            continue;
        }

        $id = $user['id'];

        // update notifications
        $update = $pdo->prepare("
        UPDATE dbpersons
        SET Notifications = 0
        WHERE id = :id
    ");
        $update->execute([':id' => $id]);


        //build the reply
        $name      = trim($ride['first_name'] . ' ' . $ride['last_name']);



        $body =
            "Hello {$name},\n\n" .
            "Your notifications have been turned off. \n" .
            "You can turn them back on by emailing the key word 'start' to this email address.\n" .
            "If you have any questions, please contact us directly.\n\n" .
            "Thank you,\n" .
            "Healthy Generations - Mobility Options";

        $result = sendEmailsDirect(
            [$fromEmail],
            'Mobility Options',
            'Your Notification Status',
            $body
        );

        if ($result['success']) {
            echo "→ Status reply sent to: {$fromEmail}<br>";
            logIt("Status reply sent to: {$fromEmail}");
        } else {
            echo "→ Failed to send reply to: {$fromEmail}<br>";
            logIt("Failed reply to {$fromEmail}: " . json_encode($result['failed']));
        }

        echo "<br>";
    }



    else if (
        stripos($subject, 'start') !== false ||
        stripos($body, 'start') !== false
    ) {
        $pdo = getDb();

        $stmt = $pdo->prepare("
        SELECT id, first_name, last_name
        FROM dbpersons
        WHERE LOWER(email) = :email
        LIMIT 1
    ");
        $stmt->execute([':email' => $fromEmail]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            continue;
        }

        $id = $user['id'];

        // update notifications
        $update = $pdo->prepare("
        UPDATE dbpersons
        SET Notifications = 1
        WHERE id = :id
    ");
        $update->execute([':id' => $id]);


        //build the reply
        $name      = trim($ride['first_name'] . ' ' . $ride['last_name']);

        $body =
            "Hello {$name},\n\n" .
            "Your notifications have been turned on. \n" .
            "You can turn them back off by emailing the key word 'stop' to this email address.\n" .
            "If you have any questions, please contact us directly.\n\n" .
            "Thank you,\n" .
            "Healthy Generations - Mobility Options";

        $result = sendEmailsDirect(
            [$fromEmail],
            'Mobility Options',
            'Your Notification Status',
            $body
        );

        if ($result['success']) {
            echo "→ Status reply sent to: {$fromEmail}<br>";
            logIt("Status reply sent to: {$fromEmail}");
        } else {
            echo "→ Failed to send reply to: {$fromEmail}<br>";
            logIt("Failed reply to {$fromEmail}: " . json_encode($result['failed']));
        }

        echo "<br>";
    }
}

imap_close($inbox);
echo "Done.";
