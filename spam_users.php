<?php

require_once __DIR__ . '/init.php';

$adminUsername = 'tech';

$limitstart = 0;
$limitnum   = 100;
$found      = 0;

echo "<pre>";

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

        // ⭐ FILTER — firstname contains * * *
        if (str_contains($user['firstname'], '* * *')) {

            $found++;

            echo "User ID: " . $user['id'] . "\n";
            echo "First Name: " . $user['firstname'] . "\n";
            echo "Last Name: " . $user['lastname'] . "\n";
            echo "Email: " . $user['email'] . "\n";
            echo "Created: " . $user['datecreated'] . "\n";

            if (!empty($user['clients'])) {
                foreach ($user['clients'] as $client) {
                    echo "Client ID: " . $client['id'] . "\n";
                }
            }

            echo "-----------------------------\n";
        }
    }

    $limitstart += $limitnum;

} while ($limitstart < $results['totalresults']);

echo "TOTAL FOUND: $found\n";
echo "FINISHED";
echo "</pre>";