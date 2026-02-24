<?php

require_once __DIR__ . '/init.php';

$adminUsername = 'tech';
$inputFile = __DIR__ . '/zspam_client_ids.txt';

if (!file_exists($inputFile)) {
    die("Client ID file not found\n");
}

$ids = file($inputFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$deleted = 0;
$failed  = 0;

echo "<pre>";
echo "STARTING BULK DELETE\n\n";

foreach ($ids as $clientId) {

    $clientId = trim($clientId);

    if (!is_numeric($clientId)) {
        continue;
    }

    $result = localAPI('DeleteClient', [
        'clientid'           => $clientId,
        'deleteusers'        => true,
        'deletetransactions' => true
    ], $adminUsername);

    if ($result['result'] === 'success') {
        echo "Deleted Client ID: $clientId\n";
        $deleted++;
    } else {
        echo "FAILED Client ID: $clientId\n";
        $failed++;
    }
}

echo "\nTOTAL DELETED: $deleted";
echo "\nFAILED: $failed";
echo "\nDONE";
echo "</pre>";