<?php
header('Content-Type: application/json');
set_time_limit(120);
ini_set('display_errors', 0);


try {
$input = file_get_contents('php://input');
$decoded = json_decode($input, true);


if (!$decoded) {
throw new Exception("Invalid JSON payload");
}


$python = "/usr/bin/python3";
$script = __DIR__ . "/send_email.py";


$cmd = escapeshellcmd($python . " " . $script);


$proc = proc_open($cmd, [
0 => ["pipe", "r"],
1 => ["pipe", "w"],
2 => ["pipe", "w"]
], $pipes);


if (!is_resource($proc)) {
echo json_encode(["success" => false, "error" => "Failed to start Python process"]);
exit;
}


fwrite($pipes[0], json_encode($decoded));
fflush($pipes[0]);
fclose($pipes[0]);


$output = stream_get_contents($pipes[1]);
$error = stream_get_contents($pipes[2]);


fclose($pipes[1]);
fclose($pipes[2]);


$exit_code = proc_close($proc);


// Return whatever python returned (or an error wrapper)
$decoded_output = json_decode($output, true);
if ($decoded_output) {
echo json_encode($decoded_output);
} else {
echo json_encode(["success" => false, "error" => trim($error), "raw_output" => $output]);
}


} catch (Throwable $e) {
echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
