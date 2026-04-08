<?php
require_once(__DIR__ . '/database/dbinfo.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


function retrieveAllEmails(array $ids = []): array {
    $conn = connect();
    $emails = [];

    // Ensure NULL → empty array
    if (!is_array($ids)) {
        $ids = [];
    }

    // --- ALL members ---
    if (empty($ids)) {
        $query = "SELECT id, email FROM dbpersons WHERE email IS NOT NULL AND email != ''";
        $res = $conn->query($query);
        while ($row = $res->fetch_assoc()) {
            $emails[$row['id']] = $row['email'];
        }
        return $emails;
    }

    // --- SPECIFIC members ---
    // DO NOT convert to int — IDs are VARCHAR
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('s', count($ids));  // <-- string params!

    $sql = "SELECT id, email 
            FROM dbpersons 
            WHERE id IN ($placeholders) 
            AND email IS NOT NULL 
            AND email != ''";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        return [];
    }

    // Prepare binding references
    $params = [ &$types ];
    foreach ($ids as $k => $v) {
        $params[] = &$ids[$k];
    }

    call_user_func_array([$stmt, 'bind_param'], $params);

    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $emails[$row['id']] = $row['email'];
    }

    $stmt->close();
    return $emails;
}

function sendEmails(array $emails, string $senderName, string $subject, string $body): array {
    require_once __DIR__ . '/email/sendEmail.php';

    $result = sendEmailsDirect($emails, $senderName, $subject, $body);

    // Log for debugging
    $logFile = __DIR__ . '/email_debug.log';
    if (is_writable(__DIR__)) {
        $log = "[sendEmails] Direct call | Result: " . json_encode($result) . "\n";
        file_put_contents($logFile, $log, FILE_APPEND);
    }

    return $result;
}





