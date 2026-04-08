<?php
// -------------------------------------------------------
// Load PHPMailer
// -------------------------------------------------------
require_once __DIR__ . "/PHPMailer/PHPMailer/src/PHPMailer.php";
require_once __DIR__ . "/PHPMailer/PHPMailer/src/SMTP.php";
require_once __DIR__ . "/PHPMailer/PHPMailer/src/Exception.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// -------------------------------------------------------
// Callable function (used by email.php directly)
// -------------------------------------------------------
function sendEmailsDirect(array $emails, string $senderName, string $subject, string $body): array {
    $SMTP_SERVER = 'smtp.gmail.com';
    $SMTP_PORT   = 587;
    $SMTP_USER   = 'mobilityoptions.notifications@gmail.com';
    $SMTP_PASS   = 'pbhg xxgo ejop ycgt';

    $sent   = [];
    $failed = [];

    foreach ($emails as $email) {
        try {
            $mail = new PHPMailer(true);

            $mail->isSMTP();
            $mail->Host       = $SMTP_SERVER;
            $mail->Port       = (int)$SMTP_PORT;
            $mail->SMTPAuth   = true;
            $mail->Username   = $SMTP_USER;
            $mail->Password   = $SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

            $mail->setFrom($SMTP_USER, $senderName);
            $mail->addAddress($email);

            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->isHTML(false);

            $mail->send();
            $sent[] = $email;
        } catch (Exception $e) {
            $errorMsg = "[" . date('Y-m-d H:i:s') . "] Failed to send to {$email}: " . $e->getMessage() . PHP_EOL;
            $logFile = __DIR__ . '/email_errors.log';
            if (is_writable(__DIR__)) {
                file_put_contents($logFile, $errorMsg, FILE_APPEND);
            }
            $failed[] = [
                "email" => $email,
                "error" => $e->getMessage()
            ];
        }
    }

    return [
        "success" => count($failed) === 0,
        "sent"    => $sent,
        "failed"  => $failed,
        "error"   => count($failed) ? "Some emails failed" : ""
    ];
}

// -------------------------------------------------------
// HTTP endpoint — only runs when accessed directly via web
// -------------------------------------------------------
if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    header('Content-Type: application/json');
    set_time_limit(60);
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $raw  = file_get_contents("php://input");
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

    echo json_encode(sendEmailsDirect($emails, $senderName, $subject, $body));
}

