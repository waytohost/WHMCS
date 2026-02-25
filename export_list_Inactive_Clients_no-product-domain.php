<?php
require_once __DIR__ . '/init.php';

$adminUsername = 'tech';
$limitstart = 0;
$limitnum = 50;
$found = 0;

// output file
$outputFile = __DIR__ . '/inactive_client_ids.txt';
$file = fopen($outputFile, 'w');

if (!$file) {
    die("Cannot create output file\n");
}

echo "<pre>";

do {
    // Step 1: get clients
    $results = localAPI('GetClients', [
        'limitstart' => $limitstart,
        'limitnum' => $limitnum,
        'responsetype' => 'json'
    ], $adminUsername);

    if ($results['result'] !== 'success') {
        die("API Error\n");
    }

    foreach ($results['clients']['client'] as $client) {

        $clientId = $client['id'];

        // Step 2: get client details
        $details = localAPI('GetClientsDetails', [
            'clientid' => $clientId,
            'responsetype' => 'json'
        ], $adminUsername);

        if ($details['status'] !== 'Inactive') continue;

        // Step 3: check client products
        $products = localAPI('GetClientsProducts', [
            'clientid' => $clientId,
            'responsetype' => 'json'
        ], $adminUsername);

        $activeProducts = 0;
        if (!empty($products['products']['product'])) {
            foreach ($products['products']['product'] as $p) {
                if ($p['status'] === 'Active') $activeProducts++;
            }
        }
        if ($activeProducts > 0) continue;

        // Step 4: check client domains
        $domains = localAPI('GetClientsDomains', [
            'clientid' => $clientId,
            'responsetype' => 'json'
        ], $adminUsername);

        $domainCount = 0;
        if (!empty($domains['domains']['domain'])) {
            $domainCount = count($domains['domains']['domain']);
        }
        if ($domainCount > 0) continue;

        // ✅ client is completely inactive
        $found++;
        echo "Client ID: $clientId - " . $details['firstname'] . " " . $details['lastname'] . "\n";

        // write client ID to file
        fwrite($file, $clientId . PHP_EOL);
    }

    $limitstart += $limitnum;

} while ($limitstart < $results['totalresults']);

fclose($file);

echo "\nTOTAL FOUND: $found\n";
echo "Saved to: inactive_client_ids.txt\n";
echo "DONE";
echo "</pre>";