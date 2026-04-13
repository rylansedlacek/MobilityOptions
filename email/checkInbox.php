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
define('DB_USER', 'root');
define('DB_PASS', 'root');

function getDb(): PDO {
    static $pdo = null;
    if (!$pdo) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8',
            DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }
    return $pdo;
}
//error catching and logging
function logIt(string $msg): void {
    file_put_contents(__DIR__ . '/inbox_errors.log',
        '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL, FILE_APPEND);
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

if (stripos($subject, 'status') === false && stripos($body, 'status') === false) {
    echo "→ Skipped (no 'status' keyword in subject or body)<br><br>";
    continue;
}

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
            e.trip_status
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
    $status    = $ride['trip_status'];
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
        echo "→ Status {$trip_status}<br>";
        logIt("Status reply sent to: {$fromEmail}");
    } else {
        echo "→ Failed to send reply to: {$fromEmail}<br>";
        logIt("Failed reply to {$fromEmail}: " . json_encode($result['failed']));
    }

    echo "<br>";
}

imap_close($inbox);
echo "Done.";