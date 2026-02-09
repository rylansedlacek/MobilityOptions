<?php
header('Content-Type: application/json');
set_time_limit(60);
error_reporting(E_ALL);
ini_set('display_errors', 1);

// -------------------------------------------------------
// Load .env file (must be in parent directory)
// -------------------------------------------------------
$envPath = "/home/customer/www/jenniferp217.sg-host.com/public_html/email/.env";
if (!file_exists($envPath)) {
    echo json_encode(["success" => false, "error" => ".env file not found"]);
    exit;
}

$env = parse_ini_file($envPath);

$SMTP_SERVER = $env["SMTP_SERVER"] ?? null;
$SMTP_PORT   = $env["SMTP_PORT"] ?? 587;
$SMTP_USER   = $env["SMTP_USER"] ?? null;
$SMTP_PASS   = $env["SMTP_PASS"] ?? null;

if (!$SMTP_SERVER || !$SMTP_USER || !$SMTP_PASS) {
    echo json_encode(["success" => false, "error" => "Missing SMTP settings in .env"]);
    exit;
}

// -------------------------------------------------------
// Read JSON input sent via cURL
// -------------------------------------------------------
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!$data || !isset($data["emails"])) {
    echo json_encode(["success" => false, "error" => "Invalid JSON payload"]);
    exit;
}

$emails     = $data["emails"];
$senderName = $data["senderName"] ?? "No Name";
$subject    = $data["subject"] ?? "";
$body       = $data["body"] ?? "";

if (trim($subject) === "") {
    echo json_encode(["success" => false, "error" => "Missing email subject"]);
    exit;
}

// -------------------------------------------------------
// Load PHPMailer
// -------------------------------------------------------
require_once __DIR__ . "/PHPMailer/src/PHPMailer.php";
require_once __DIR__ . "/PHPMailer/src/SMTP.php";
require_once __DIR__ . "/PHPMailer/src/Exception.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$sent   = [];
$failed = [];

// -------------------------------------------------------
// Send emails one-by-one
// -------------------------------------------------------
foreach ($emails as $email) {
    try {
        $mail = new PHPMailer(true);

        // SMTP Setup
        $mail->isSMTP();
        $mail->Host       = $SMTP_SERVER;
        $mail->Port       = (int)$SMTP_PORT;
        $mail->SMTPAuth   = true;
        $mail->Username   = $SMTP_USER;
        $mail->Password   = $SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        // Email headers
        $mail->setFrom($SMTP_USER, $senderName);
        $mail->addAddress($email);

        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->isHTML(false);

        // send
        $mail->send();

        $sent[] = $email;
    } catch (Exception $e) {
        $errorMsg = "[" . date('Y-m-d H:i:s') . "] Failed to send to {$email}: " . $e->getMessage() . PHP_EOL;
        
        // Write to email_errors.log file (ensure this path is writable)
        file_put_contents(__DIR__ . '/email/email_errors.log', $errorMsg, FILE_APPEND);

        $failed[] = [
            "email" => $email,
            "error" => $e->getMessage()
        ];
    }
}


// -------------------------------------------------------
// Final Output
// -------------------------------------------------------
echo json_encode([
    "success" => count($failed) === 0,
    "sent"    => $sent,
    "failed"  => $failed,
    "error"   => count($failed) ? "Some emails failed" : ""
]);

