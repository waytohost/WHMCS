<?php
require_once __DIR__ . '/init.php';

$adminUsername = 'tech';
$limitstart = 0;
$limitnum = 50;
$found = 0;

$outputFile = __DIR__ . '/inactive_client_ids.txt';
$file = fopen($outputFile, 'w');

if (!$file) {
    die("Cannot create output file\n");
}

echo "<pre>";

do {
    $results = localAPI('GetClients', [
        'limitstart' => $limitstart,
        'limitnum' => $limitnum,
        'responsetype' => 'json'
    ], $adminUsername);

    if ($results['result'] !== 'success') {
        die("API Error\n");
    }

    // Handle single vs multiple clients
    $clients = [];
    if (isset($results['clients']['client'][0])) {
        $clients = $results['clients']['client'];
    } elseif (isset($results['clients']['client']['id'])) {
        $clients[] = $results['clients']['client'];
    }

    foreach ($clients as $client) {
        $clientId = $client['id'];

        $details = localAPI('GetClientsDetails', [
            'clientid' => $clientId,
            'responsetype' => 'json'
        ], $adminUsername);

        if ($details['status'] !== 'Inactive') continue;

        // Check products
        $products = localAPI('GetClientsProducts', [
            'clientid' => $clientId,
            'responsetype' => 'json'
        ], $adminUsername);

        $activeProducts = 0;
        if (!empty($products['products']['product'])) {
            $productList = $products['products']['product'];
            if (isset($productList['id'])) $productList = [$productList]; // single product
            foreach ($productList as $p) {
                if ($p['status'] === 'Active') $activeProducts++;
            }
        }
        if ($activeProducts > 0) continue;

        // Check domains
        $domains = localAPI('GetClientsDomains', [
            'clientid' => $clientId,
            'responsetype' => 'json'
        ], $adminUsername);

        $domainCount = 0;
        if (!empty($domains['domains']['domain'])) {
            $domainList = $domains['domains']['domain'];
            if (isset($domainList['id'])) $domainList = [$domainList];
            $domainCount = count($domainList);
        }
        if ($domainCount > 0) continue;

        // ✅ completely inactive client
        $found++;

        // Pretty output
        echo "Client ID: $clientId\n";
        echo "Name: " . $details['firstname'] . " " . $details['lastname'] . "\n";
        echo "Email: " . $details['email'] . "\n";
        echo "Active Services: $activeProducts\n";
        echo "Domains: $domainCount\n";
        echo "Status: " . $details['status'] . "\n";
        echo "-----------------------------\n";

        // Save Client ID only
        fwrite($file, $clientId . PHP_EOL);
    }

    $limitstart += $limitnum;

} while ($limitstart < $results['totalresults']);

fclose($file);

echo "\nTOTAL FOUND: $found\n";
echo "Client IDs saved to: inactive_client_ids.txt\n";
echo "DONE";
echo "</pre>";