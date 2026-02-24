<?php

require_once __DIR__ . '/init.php';

$adminUsername = 'tech';

// 👉 where to save the file
$outputFile = __DIR__ . '/zspam_client_ids.txt';

$limitstart = 0;
$limitnum   = 100;
$totalSaved = 0;

// open file for writing
$file = fopen($outputFile, 'w');

if (!$file) {
    die("Cannot create output file\n");
}

do {
    $results = localAPI('GetUsers', [
        'limitstart'   => $limitstart,
        'limitnum'     => $limitnum,
        'responsetype' => 'json'
    ], $adminUsername);

    if (!is_array($results) || $results['result'] !== 'success') {
        echo "API ERROR\n";
        print_r($results);
        exit;
    }

    foreach ($results['users'] as $user) {

        // spam pattern
        if (str_contains($user['firstname'], '* * *')) {

            if (!empty($user['clients'])) {
                foreach ($user['clients'] as $client) {
                    fwrite($file, $client['id'] . PHP_EOL);
                    $totalSaved++;
                }
            }
        }
    }

    $limitstart += $limitnum;

} while ($limitstart < $results['totalresults']);

fclose($file);

echo "<pre>";
echo "Saved Client IDs: $totalSaved\n";
echo "File: spam_client_ids.txt\n";
echo "DONE";
echo "</pre>";