<?php
// include/event-functions.php
require_once __DIR__ . '/database/dbinfo.php'; // DB connection
require_once __DIR__ . '/email.php';           // email sending function
require_once __DIR__ . '/database/dbEvents.php'; // getPAttendance

$conn = connect();

function scheduleEventEmails($userID, $event) {
    global $conn;

    // Fetch recipient userIDs for this event
    $recipientIDs = getPAttendance($event['id']);

    $eventDateTime = $event['startDate'] . ' ' . ($event['startTime'] ?? '00:00:00');

    $oneWeekBefore = date('Y-m-d H:i:s', strtotime($eventDateTime . ' -7 days'));
    $oneDayBefore  = date('Y-m-d H:i:s', strtotime($eventDateTime . ' -1 day'));

    $emails = [
        [
            'scheduledSend' => $oneWeekBefore,
            'subject' => "Reminder: {$event['name']} in 1 week",
            'body' => "This is a reminder that the event '{$event['name']}' is coming up on {$eventDateTime}.",
        ],
        [
            'scheduledSend' => $oneDayBefore,
            'subject' => "Reminder: {$event['name']} tomorrow",
            'body' => "This is a reminder that the event '{$event['name']}' is happening tomorrow ({$eventDateTime}).",
        ]
    ];

    // Prepare statement once
    $stmt = $conn->prepare("
        INSERT INTO dbscheduledemails (userID, recipientID, subject, body, scheduledSend, sent)
        VALUES (?, ?, ?, ?, ?, 0)
    ");

    if (!$stmt) {
        throw new Exception("DB prepare failed: " . $conn->error);
    }

    // Loop through all emails and recipients
    foreach ($emails as $email) {
        foreach ($recipientIDs as $recipientID) {
            $stmt->bind_param(
                "sssss",
                $userID,
                $recipientID,
                $email['subject'],
                $email['body'],
                $email['scheduledSend']
            );
            $stmt->execute();
        }
    }

    $stmt->close();
}

