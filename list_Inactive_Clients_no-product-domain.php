<?php
require_once __DIR__ . '/init.php';

$adminUsername = 'tech';
$limitstart = 0;
$limitnum = 50;
$found = 0;

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

        if ($details['status'] !== 'Inactive') {
            continue; // only inactive accounts
        }

        // Step 3: check client products
        $products = localAPI('GetClientsProducts', [
            'clientid' => $clientId,
            'responsetype' => 'json'
        ], $adminUsername);

        $activeProducts = 0;
        if (!empty($products['products']['product'])) {
            foreach ($products['products']['product'] as $p) {
                if ($p['status'] === 'Active') {
                    $activeProducts++;
                }
            }
        }

        if ($activeProducts > 0) continue; // skip if they have active products

        // Step 4: check client domains
        $domains = localAPI('GetClientsDomains', [
            'clientid' => $clientId,
            'responsetype' => 'json'
        ], $adminUsername);

        $domainCount = 0;
        if (!empty($domains['domains']['domain'])) {
            $domainCount = count($domains['domains']['domain']);
        }

        if ($domainCount > 0) continue; // skip if they have any domains

        // ✅ client is completely inactive
        $found++;
        echo "Client ID: " . $clientId . "\n";
        echo "Name: " . $details['firstname'] . " " . $details['lastname'] . "\n";
        echo "Email: " . $details['email'] . "\n";
        echo "Active Services: $activeProducts\n";
        echo "Domains: $domainCount\n";
        echo "Status: " . $details['status'] . "\n";
        echo "-----------------------------\n";

    }

    $limitstart += $limitnum;

} while ($limitstart < $results['totalresults']);

echo "TOTAL FOUND: $found\n";
echo "FINISHED";
echo "</pre>";