<?php
session_cache_expire(30);
session_start();
ini_set("display_errors",1);
error_reporting(E_ALL);

if (!isset($_SESSION['_id'])) {
    header('Location: login.php');
    exit;
}

require_once(__DIR__ . '/database/dbinfo.php');

// PHPMailer
require_once __DIR__ . '/email/PHPMailer/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/email/PHPMailer/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/email/PHPMailer/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/* ---------------------------------------------
   Load .env (same function as createEmail.php)
--------------------------------------------- */
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

/* ---------------------------------------------
   PHPMailer send function (exactly same code)
--------------------------------------------- */
function sendEmails(array $emails, string $subject, string $body): array {
    global $env;
    $results = [];
    $success = true;

    foreach ($emails as $email) {
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
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
            $results[] = ["email" => $email, "success" => true];

        } catch (Exception $e) {
            $success = false;
            $results[] = ["email" => $email, "success" => false, "error" => $mail->ErrorInfo];
        }
    }

    return ['success' => $success, 'results' => $results];
}

/* ---------------------------------------------
   Retrieve emails like createEmail.php
--------------------------------------------- */
function getEmailsByIDs(array $ids): array {
    $conn = connect();
    $emails = [];

    if (empty($ids)) return [];

    foreach ($ids as $id) {
        $stmt = $conn->prepare("SELECT email FROM dbpersons WHERE id = ? AND email IS NOT NULL AND email != ''");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            if (filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                $emails[] = $row['email'];
            }
        }
        $stmt->close();
    }

    return $emails;
}

function getAllEmails(): array {
    $conn = connect();
    $emails = [];
    $res = $conn->query("SELECT email FROM dbpersons WHERE email IS NOT NULL AND email != ''");

    while ($row = $res->fetch_assoc()) {
        if (filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
            $emails[] = $row['email'];
        }
    }
    return $emails;
}

/* ---------------------------------------------
   Begin logic for sending the draft
--------------------------------------------- */
if (!isset($_GET['id'])) {
    header('Location: drafts.php?msg=Invalid+Draft+ID');
    exit;
}

$draftId = intval($_GET['id']);
$userId  = $_SESSION['_id'];

$conn = connect();

/* Load the draft */
$stmt = $conn->prepare("SELECT subject, body, recipientID FROM dbdrafts WHERE draftID = ? AND userID = ?");
$stmt->bind_param("is", $draftId, $userId);
$stmt->execute();
$res = $stmt->get_result();
$draft = $res->fetch_assoc();
$stmt->close();

if (!$draft) {
    mysqli_close($conn);
    header('Location: drafts.php?msg=Draft+not+found');
    exit;
}

$recipientID = trim($draft['recipientID']);
$emails = [];

/* ---------------------------------------------
   RECIPIENT TYPE HANDLING — SAME RULES AS CREATEEMAIL
--------------------------------------------- */

if (strtolower($recipientID) === "all") {
    // send to all members
    $emails = getAllEmails();

} else {
    // could be a single ID or comma-separated IDs
    $ids = array_map('trim', explode(",", $recipientID));
    $emails = getEmailsByIDs($ids);
}

if (empty($emails)) {
    mysqli_close($conn);
    header("Location: drafts.php?msg=No+valid+recipients");
    exit;
}

/* ---------------------------------------------
   SEND THE EMAILS
--------------------------------------------- */
$results = sendEmails($emails, $draft['subject'], $draft['body']);

$deleteQuery = "DELETE FROM dbdrafts WHERE draftID = ?";
$deleteStmt = $conn->prepare($deleteQuery);
$deleteStmt->bind_param("i", $draftId);
$deleteStmt->execute();
$deleteStmt->close();

mysqli_close($conn);

$successCount = 0;
$failureCount = 0;

foreach ($results['results'] as $r) {
    if ($r['success']) $successCount++;
    else $failureCount++;
}


header("Location: viewDrafts.php?msg=Sent+$successCount,+Failed+$failureCount");
exit;



