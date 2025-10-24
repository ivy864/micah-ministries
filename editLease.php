<?php
session_cache_expire(30);
session_start();

$loggedIn = false;
$accessLevel = 0;
$userID = null;

if (isset($_SESSION['_id'])) {
    $loggedIn = true;
    $accessLevel = $_SESSION['access_level'];
    $userID = $_SESSION['_id'];
}

if ($accessLevel < 2) {
    header('Location: index.php');
    die();
}

$pdo = null;
$db_notice = null;
$lease = [
    'tenant_name' => '',
    'property_address' => '',
    'unit_number' => '',
    'start_date' => '',
    'expiration_date' => '',
    'monthly_rent' => '',
    'security_deposit' => ''
];

try {
    $conf = null;
    $confPath = __DIR__ . '/database/dbViewLease.php.'; // change to actual db

    if (file_exists($confPath)) {
        include $confPath; // sets $conf
    }

    $dsn  = $conf['dsn']  ?? getenv('MICAH_DB_DSN');
    $user = $conf['user'] ?? getenv('MICAH_DB_USER');
    $pass = $conf['pass'] ?? getenv('MICAH_DB_PASS');

    if ($dsn) {
        $pdo = new PDO($dsn, $user ?: null, $pass ?: null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } else {
        $db_notice = "Database not yet configured — please fill in database/dbViewLease.php."; // change to actual db
    }
} catch (Throwable $e) {
    $db_notice = "Database connection failed: " . htmlspecialchars($e->getMessage());
}

$lease_id = $_GET['id'] ?? null;

if ($pdo && $lease_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT tenant_name, property_address, unit_number, start_date, 
                   expiration_date, monthly_rent, security_deposit 
            FROM leases 
            WHERE id = :id 
            LIMIT 1
        ");
        $stmt->execute([':id' => $lease_id]);
        if ($row = $stmt->fetch()) {
            $lease = array_merge($lease, $row);
        } else {
            $db_notice = "No lease found for ID " . htmlspecialchars($lease_id);
        }
    } catch (Throwable $e) {
        $db_notice = "Error loading lease: " . htmlspecialchars($e->getMessage());
    }
}

if ($pdo && $_SERVER['REQUEST_METHOD'] === 'POST' && $lease_id) {
    $fields = [
        'tenant_name',
        'property_address',
        'unit_number',
        'start_date',
        'expiration_date',
        'monthly_rent',
        'security_deposit'
    ];
    $data = [];

    foreach ($fields as $f) {
        if (isset($_POST[$f])) {
            $data[$f] = $_POST[$f];
        }
    }

    if (!empty($data)) {
        $sets = [];
        $params = [':id' => $lease_id];

        foreach ($data as $k => $v) {
            $sets[] = "$k = :$k";
            $params[":$k"] = $v;
        }

        try {
            $sql = "UPDATE leases SET " . implode(", ", $sets) . " WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $lease = array_merge($lease, $data);
            $db_notice = "✅ Lease updated successfully.";
        } catch (Throwable $e) {
            $db_notice = "❌ Update failed: " . htmlspecialchars($e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Micah Ministries | Edit Lease</title>
    <link href="css/normal_tw.css" rel="stylesheet">
    <?php
    $tailwind_mode = true;
    require_once('header.php');
    ?>
    <style>
        .date-box {
            background: #274471;
            padding: 7px 30px;
            border-radius: 50px;
            box-shadow: -4px 4px 4px rgba(0, 0, 0, 0.25) inset;
            color: white;
            font-size: 24px;
            font-weight: 700;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="hero-header">
        <div class="center-header">
            <h1>Edit a Lease</h1>
        </div>
    </div>

    <main>
        <div class="main-content-box p-6">
            <?php if (isset($db_notice) && $db_notice): ?>
                <div class="p-3 mb-4 rounded-md <?php echo ($pdo ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'); ?>">
                    <?php echo $db_notice; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-4">
                <div>
                    <label for="tenant_name" class="block font-medium text-gray-700">Tenant Name:</label>
                    <input type="text" id="tenant_name" name="tenant_name"
                        value="<?php echo htmlspecialchars($lease['tenant_name']); ?>"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                </div>

                <div>
                    <label for="property_address" class="block font-medium text-gray-700">Property Address:</label>
                    <input type="text" id="property_address" name="property_address"
                        value="<?php echo htmlspecialchars($lease['property_address']); ?>"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                </div>

                <div>
                    <label for="unit_number" class="block font-medium text-gray-700">Unit Number:</label>
                    <input type="text" id="unit_number" name="unit_number"
                        value="<?php echo htmlspecialchars($lease['unit_number']); ?>"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                </div>

                <div>
                    <label for="start_date" class="block font-medium text-gray-700">Lease Start Date:</label>
                    <input type="date" id="start_date" name="start_date"
                        value="<?php echo htmlspecialchars($lease['start_date']); ?>"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                </div>

                <div>
                    <label for="expiration_date" class="block font-medium text-gray-700">Lease End Date:</label>
                    <input type="date" id="expiration_date" name="expiration_date"
                        value="<?php echo htmlspecialchars($lease['expiration_date']); ?>"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                </div>

                <div>
                    <label for="monthly_rent" class="block font-medium text-gray-700">Rent Amount:</label>
                    <input type="number" step="0.01" id="monthly_rent" name="monthly_rent"
                        value="<?php echo htmlspecialchars($lease['monthly_rent']); ?>"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                </div>

                <div>
                    <label for="security_deposit" class="block font-medium text-gray-700">Security Deposit:</label>
                    <input type="number" step="0.01" id="security_deposit" name="security_deposit"
                        value="<?php echo htmlspecialchars($lease['security_deposit']); ?>"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                </div>

                <div>
                    <button type="submit" class="blue-button">Submit Changes</button>
                </div>
            </form>

            <div class="mt-10 flex justify-end">
                <a href="micahportal.php" class="blue-button">Return to Dashboard</a>
            </div>
        </div>
    </main>
</body>
</html>