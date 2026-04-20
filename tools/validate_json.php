<?php
$s = file_get_contents($argv[1]);
json_decode($s);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "OK\n";
    exit(0);
}
echo json_last_error_msg() . "\n";
// Attempt naive line display
$lines = explode("\n", $s);
for ($i = 0; $i < count($lines); $i++) {
    echo sprintf("%4d: %s\n", $i + 1, $lines[$i]);
}
exit(2);
