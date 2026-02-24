# 🧾 WHMCS Spam Account Cleanup — Admin Wiki

## Overview

This toolkit performs:

1️⃣ Identify spam users
2️⃣ Export spam Client IDs
3️⃣ Test deletion safely
4️⃣ Bulk delete spam clients

Spam pattern used:
👉 firstname contains `* * *`

All scripts must be placed in the WHMCS root directory (same folder as `configuration.php`).

Admin username used for API:

```
tech
```

---

# 📄 Script 1 — List Spam Users

## Purpose

Lists only users whose firstname contains spam pattern.

## File

```
list_spam_users.php
```

## Code

```php
<?php
require_once __DIR__ . '/init.php';

$adminUsername = 'tech';

$limitstart = 0;
$limitnum   = 100;
$found      = 0;

echo "<pre>";

do {
    $results = localAPI('GetUsers', [
        'limitstart' => $limitstart,
        'limitnum'   => $limitnum,
    ], $adminUsername);

    foreach ($results['users'] as $user) {

        if (str_contains($user['firstname'], '* * *')) {

            $found++;

            echo "User ID: " . $user['id'] . "\n";
            echo "First Name: " . $user['firstname'] . "\n";
            echo "Email: " . $user['email'] . "\n";

            foreach ($user['clients'] as $client) {
                echo "Client ID: " . $client['id'] . "\n";
            }

            echo "------------------\n";
        }
    }

    $limitstart += $limitnum;

} while ($limitstart < $results['totalresults']);

echo "TOTAL FOUND: $found";
echo "</pre>";
```

---

# 📄 Script 2 — Export Spam Client IDs

## Purpose

Saves only Client IDs of spam users to a file.

## File

```
export_spam_client_ids.php
```

## Output file

```
spam_client_ids.txt
```

## Code

```php
<?php
require_once __DIR__ . '/init.php';

$adminUsername = 'tech';
$outputFile = __DIR__ . '/spam_client_ids.txt';

$limitstart = 0;
$limitnum   = 100;

$file = fopen($outputFile, 'w');

do {
    $results = localAPI('GetUsers', [
        'limitstart' => $limitstart,
        'limitnum'   => $limitnum,
    ], $adminUsername);

    foreach ($results['users'] as $user) {

        if (str_contains($user['firstname'], '* * *')) {

            foreach ($user['clients'] as $client) {
                fwrite($file, $client['id'] . PHP_EOL);
            }
        }
    }

    $limitstart += $limitnum;

} while ($limitstart < $results['totalresults']);

fclose($file);

echo "Client IDs exported";
```

---

# 📄 Script 3 — Test Delete (Safety Check)

## Purpose

Deletes only ONE client to confirm API works.

## File

```
test_delete_one.php
```

## Code

```php
<?php
require_once __DIR__ . '/init.php';

$adminUsername = 'tech';
$inputFile = __DIR__ . '/spam_client_ids.txt';

$ids = file($inputFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$clientId = trim($ids[0]);

echo "<pre>";
echo "Deleting Client ID: $clientId\n";

$result = localAPI('DeleteClient', [
    'clientid'           => $clientId,
    'deleteusers'        => true,
    'deletetransactions' => true
], $adminUsername);

print_r($result);
echo "</pre>";
```

---

# 📄 Script 4 — Bulk Delete Spam Clients

## Purpose

Deletes all Client IDs stored in file.

## File

```
delete_all_spam_clients.php
```

## Code

```php
<?php
require_once __DIR__ . '/init.php';

$adminUsername = 'tech';
$inputFile = __DIR__ . '/spam_client_ids.txt';

$ids = file($inputFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$deleted = 0;
$failed  = 0;

echo "<pre>";

foreach ($ids as $clientId) {

    $clientId = trim($clientId);

    $result = localAPI('DeleteClient', [
        'clientid'           => $clientId,
        'deleteusers'        => true,
        'deletetransactions' => true
    ], $adminUsername);

    if ($result['result'] === 'success') {
        echo "Deleted: $clientId\n";
        $deleted++;
    } else {
        echo "FAILED: $clientId\n";
        $failed++;
    }
}

echo "\nTOTAL DELETED: $deleted";
echo "\nFAILED: $failed";
echo "</pre>";
```

---

# 🔐 Security Procedure

After cleanup:

✔ Delete all PHP scripts
✔ Delete spam_client_ids.txt
✔ Enable CAPTCHA in WHMCS
✔ Enable Email Verification
✔ Disable free registration

---

# 🧪 Execution Order

1️⃣ list_spam_users.php
2️⃣ export_spam_client_ids.php
3️⃣ test_delete_one.php
4️⃣ delete_all_spam_clients.php



Just tell me what format you want.
