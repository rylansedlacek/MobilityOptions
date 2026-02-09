<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

file_put_contents(__DIR__ . '/cron_debug.log', date('Y-m-d H:i:s') . " - Script started\n", FILE_APPEND);

/**
 * scheduledSend.php
 * CLI/cron-ready script to send scheduled emails
 */

ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/scheduledSend_errors.log');
error_reporting(E_ALL);

// Simple test log to verify cron runs
file_put_contents(__DIR__ . '/test.log', date('Y-m-d H:i:s') . " - Script started\n", FILE_APPEND);

// ----------------------
// Load .env
// ----------------------
function loadEnv(string $file): array {
    $env = [];
    if (!file_exists($file)) return $env;
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        [$key, $value] = explode('=', $line, 2);
        $env[trim($key)] = trim($value);
    }
    return $env;
}

$env = loadEnv(__DIR__ . '/email/.env');

// ----------------------
// Database connection
// ----------------------
require_once __DIR__ . '/database/dbinfo.php';
$conn = connect();

// ----------------------
// Fetch scheduled emails
// ----------------------
$stmt = $conn->prepare("
    SELECT id, recipientID, subject, body 
    FROM dbscheduledemails 
    WHERE sent = 0 AND scheduledSend <= NOW()
");
$stmt->execute();
$res = $stmt->get_result();

$emailsToSend = [];
while ($row = $res->fetch_assoc()) {
    $emailsToSend[] = $row;
}
$stmt->close();

// ----------------------
// Helper: get recipient email
// ----------------------
function getRecipientEmail($recipientID) {
    global $conn;
    $stmt = $conn->prepare("SELECT email FROM dbpersons WHERE id = ?");
    $stmt->bind_param("s", $recipientID);
    $stmt->execute();
    $stmt->bind_result($email);
    $stmt->fetch();
    $stmt->close();
    return $email;
}
file_put_contents(__DIR__ . '/cron_debug.log', "3: Before PHPMailer include\n", FILE_APPEND);
// ----------------------
// PHPMailer setup
// ----------------------
require_once __DIR__ . '/email/PHPMailer/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/email/PHPMailer/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/email/PHPMailer/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
file_put_contents(__DIR__ . '/cron_debug.log', "4: PHPMailer loaded\n", FILE_APPEND);
// ----------------------
// Send scheduled emails
// ----------------------
file_put_contents(__DIR__ . '/cron_debug.log', "5: About to send email\n", FILE_APPEND);
foreach ($emailsToSend as $emailData) {

    $recipientEmail = getRecipientEmail($emailData['recipientID']);
    if (!$recipientEmail) continue;

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $env['SMTP_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $env['SMTP_USER'];
        $mail->Password   = $env['SMTP_PASS'];
        $mail->SMTPSecure = 'tls';
        $mail->Port       = $env['SMTP_PORT'];

        $mail->setFrom($env['SMTP_USER'], $env['SMTP_FROM_NAME']);
        $mail->addReplyTo($env['SMTP_USER'], $env['SMTP_FROM_NAME']);
        $mail->addAddress($recipientEmail);

        $mail->isHTML(true);
        $mail->Subject = $emailData['subject'];
        $mail->Body    = $emailData['body'];
        $mail->AltBody = strip_tags($emailData['body']); // plain-text fallback

        // Optional: log debug for troubleshooting (comment out in production)
        /*
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = function($str, $level) {
            file_put_contents(__DIR__ . '/smtp_debug.log', date('Y-m-d H:i:s') . " [$level] $str\n", FILE_APPEND);
        };
        */

        $mail->send();

        // Mark email as sent
        $stmtUpdate = $conn->prepare("UPDATE dbscheduledemails SET sent = 1 WHERE id = ?");
        $stmtUpdate->bind_param("s", $emailData['id']);
        $stmtUpdate->execute();
        $stmtUpdate->close();

        file_put_contents(__DIR__ . '/test.log', date('Y-m-d H:i:s') . " - Sent email to {$recipientEmail}\n", FILE_APPEND);

    } catch (Exception $e) {
        file_put_contents(__DIR__ . '/scheduledSend_errors.log', 
            "[".date('Y-m-d H:i:s')."] Failed to send to {$recipientEmail}: {$mail->ErrorInfo}\n", FILE_APPEND);
    }
}

$conn->close();
file_put_contents(__DIR__ . '/test.log', date('Y-m-d H:i:s') . " - Script finished\n", FILE_APPEND);

