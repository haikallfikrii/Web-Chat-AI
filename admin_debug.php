<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

header('Content-Type: text/plain');

echo "Step 1: PHP is working\n";

try {
    require_once __DIR__ . '/includes/auth.php';
    echo "Step 2: auth.php loaded\n";
} catch (Throwable $e) {
    die("Error loading auth.php: " . $e->getMessage() . "\n" . $e->getTraceAsString());
}

try {
    require_once __DIR__ . '/includes/admin.php';
    echo "Step 3: admin.php loaded\n";
} catch (Throwable $e) {
    die("Error loading admin.php: " . $e->getMessage() . "\n" . $e->getTraceAsString());
}

try {
    $user = require_platform_admin();
    echo "Step 4: Admin auth OK - User: " . ($user['email'] ?? 'unknown') . "\n";
} catch (Throwable $e) {
    die("Error in require_platform_admin: " . $e->getMessage() . "\n" . $e->getTraceAsString());
}

try {
    $pdo = get_db();
    echo "Step 5: Database connected\n";
} catch (Throwable $e) {
    die("Error getting DB: " . $e->getMessage() . "\n" . $e->getTraceAsString());
}

$client_id = (int) ($_GET['id'] ?? 0);
echo "Step 6: Client ID = $client_id\n";

if ($client_id < 1) {
    die("Error: No valid client ID provided. Use ?id=X");
}

try {
    $client = admin_get_client($pdo, $client_id);
    echo "Step 7: admin_get_client executed\n";
    
    if ($client === null) {
        die("Client not found for ID: $client_id");
    }
    
    echo "Step 8: Client found!\n";
    echo "  - Name: " . ($client['name'] ?? 'N/A') . "\n";
    echo "  - Email: " . ($client['email'] ?? 'N/A') . "\n";
    echo "  - Plan: " . ($client['plan_code'] ?? 'N/A') . "\n";
    echo "  - Status: " . ($client['subscription_status'] ?? 'N/A') . "\n";
} catch (Throwable $e) {
    die("Error in admin_get_client: " . $e->getMessage() . "\n\nTrace:\n" . $e->getTraceAsString());
}

try {
    $plan_options = admin_plan_options();
    echo "Step 9: Plan options loaded (" . count($plan_options) . " plans)\n";
} catch (Throwable $e) {
    die("Error in admin_plan_options: " . $e->getMessage() . "\n" . $e->getTraceAsString());
}

try {
    $activity = admin_client_activity_log($pdo, $client_id, 10);
    echo "Step 10: Activity log loaded (" . count($activity) . " items)\n";
} catch (Throwable $e) {
    die("Error in admin_client_activity_log: " . $e->getMessage() . "\n" . $e->getTraceAsString());
}

echo "\n=== ALL STEPS PASSED ===\n";
echo "The problem might be in the HTML rendering part of admin_client.php\n";
