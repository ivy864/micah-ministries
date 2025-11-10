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
    'tenant_first_name' => '',
    'tenant_last_name' => '',
    'property_street' => '',
    'unit_number' => '',
    'property_city' => '',
    'property_state' => '',
    'property_zip' => '',
    'start_date' => '',
    'expiration_date' => '',
    'monthly_rent' => '',
    'security_deposit' => '',
    'program_type' => '',
    'status' => 'Active'
];

try {
    $conf = null;
    $confPath = __DIR__ . '/database/dbViewLease.php';

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
            SELECT tenant_first_name, tenant_last_name, property_street, unit_number, property_city, property_state, property_zip, start_date, 
                   expiration_date, monthly_rent, security_deposit, program_type, status 
            FROM dbleases 
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
        'tenant_first_name',
        'tenant_last_name',
        'property_street',
        'property_city',
        'property_state',
        'property_zip',
        'unit_number',
        'start_date',
        'expiration_date',
        'monthly_rent',
        'security_deposit',
        'program_type',
        'status'
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
            $sql = "UPDATE dbleases SET " . implode(", ", $sets) . " WHERE id = :id";
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
<head>     <link rel="icon" type="image/png" href="images/micah-favicon.png">

    <title>Micah Ministries | Edit Lease</title>
    <link href="css/base.css?v=<?php echo time(); ?>" rel="stylesheet">
    <?php
    $tailwind_mode = true;
    require_once('header.php');
    ?>
</head>

<body>

  <!-- Hero Section - Removed to save space -->
  <!-- <header class="hero-header"></header> -->

  <!-- Main Content -->
  <main>
    <div class="sections">

      <!-- Navigation Section - Removed -->
      <div class="button-section">
       </div>

      <!-- Text Section -->
      <div class="text-section">
        <h1 class="main-text">Edit Lease</h1>
        <div class="div-blue"></div>
        <p class="secondary-text">
          Update existing lease information. Modify the fields below as needed and submit your changes to save the updated lease details.
        </p>
        
        <?php if (isset($db_notice) && $db_notice): ?>
            <div class="alert <?php echo ($pdo ? 'alert-success' : 'alert-error'); ?>">
                <?php echo $db_notice; ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form action="" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="tenant_first_name">Tenant First Name:</label>
                        <input type="text" id="tenant_first_name" name="tenant_first_name"
                            value="<?php echo htmlspecialchars($lease['tenant_first_name']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="tenant_last_name">Tenant Last Name:</label>
                        <input type="text" id="tenant_last_name" name="tenant_last_name"
                            value="<?php echo htmlspecialchars($lease['tenant_last_name']); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="property_street">Property Street:</label>
                    <input type="text" id="property_street" name="property_street"
                        value="<?php echo htmlspecialchars($lease['property_street']); ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="property_city">Property City:</label>
                        <input type="text" id="property_city" name="property_city"
                            value="<?php echo htmlspecialchars($lease['property_city']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="property_state">Property State:</label>
                        <input type="text" id="property_state" name="property_state"
                            value="<?php echo htmlspecialchars($lease['property_state']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="property_zip">Property ZIP:</label>
                        <input type="text" id="property_zip" name="property_zip"
                            value="<?php echo htmlspecialchars($lease['property_zip']); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="unit_number">Unit Number:</label>
                        <input type="text" id="unit_number" name="unit_number"
                            value="<?php echo htmlspecialchars($lease['unit_number']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="program_type">Program Type:</label>
                        <select id="program_type" name="program_type">
                            <option value="">Select Program Type</option>
                            <option value="Program #1" <?php echo ($lease['program_type'] == 'Program #1') ? 'selected' : ''; ?>>Program #1</option>
                            <option value="Program #2" <?php echo ($lease['program_type'] == 'Program #2') ? 'selected' : ''; ?>>Program #2</option>
                            <option value="Emergency Housing" <?php echo ($lease['program_type'] == 'Emergency Housing') ? 'selected' : ''; ?>>Emergency Housing</option>
                            <option value="Transitional Housing" <?php echo ($lease['program_type'] == 'Transitional Housing') ? 'selected' : ''; ?>>Transitional Housing</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status">Lease Status:</label>
                        <select id="status" name="status">
                            <option value="Active" <?php echo ($lease['status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                            <option value="Expired" <?php echo ($lease['status'] == 'Expired') ? 'selected' : ''; ?>>Expired</option>
                            <option value="Terminated" <?php echo ($lease['status'] == 'Terminated') ? 'selected' : ''; ?>>Terminated</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="start_date">Lease Start Date:</label>
                        <input type="date" id="start_date" name="start_date"
                            value="<?php echo htmlspecialchars($lease['start_date']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="expiration_date">Lease End Date:</label>
                        <input type="date" id="expiration_date" name="expiration_date"
                            value="<?php echo htmlspecialchars($lease['expiration_date']); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="monthly_rent">Rent Amount:</label>
                        <input type="number" step="0.01" id="monthly_rent" name="monthly_rent"
                            value="<?php echo htmlspecialchars($lease['monthly_rent']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="security_deposit">Security Deposit:</label>
                        <input type="number" step="0.01" id="security_deposit" name="security_deposit"
                            value="<?php echo htmlspecialchars($lease['security_deposit']); ?>">
                    </div>
                </div>

                <div style="margin-top: 30px;">
                    <button type="submit" class="blue-button">Submit Changes</button>
                </div>
                
                <div style="margin-top: 20px; text-align: center;">
                    <a href="index.php" class="gray-button">Return to Dashboard</a>
                </div>
            </form>
        </div>
      </div>
    </div>
  </main>
</body>
</html>