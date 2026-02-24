<?php

require_once __DIR__ . '/init.php';

$adminUsername = 'tech';

$limitstart = 0;
$limitnum   = 100;
$userIds    = [];

// 👉 change this to your target folder path
$outputFile = __DIR__ . '/zspam_output/spam_user_ids.txt';

// create folder if it doesn't exist
if (!is_dir(dirname($outputFile))) {
    mkdir(dirname($outputFile), 0755, true);
}

do {
    $results = localAPI('GetUsers', [
        'limitstart'   => $limitstart,
        'limitnum'     => $limitnum,
        'responsetype' => 'json'
    ], $adminUsername);

    if (!is_array($results) || $results['result'] !== 'success') {
        die("API ERROR");
    }

    foreach ($results['users'] as $user) {

        // ⭐ spam filter
        if (str_contains($user['firstname'], '* * *')) {
            $userIds[] = $user['id'];
        }
    }

    $limitstart += $limitnum;

} while ($limitstart < $results['totalresults']);

// save only IDs (one per line)
file_put_contents($outputFile, implode(PHP_EOL, $userIds));

echo "<pre>";
echo "Saved " . count($userIds) . " User IDs\n";
echo "File: $outputFile\n";
echo "DONE";
echo "</pre>";