<?php
ob_start();
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

require_once('database/dbLeases.php');

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
?>

<!DOCTYPE html>
<html lang="en">
<head>     <link rel="icon" type="image/png" href="images/micah-favicon.png">

    <title>Micah Ministries | Delete Lease</title>
    <link href="css/base.css?v=<?php echo time(); ?>" rel="stylesheet">
    <?php
    $tailwind_mode = true;
    require_once('header.php');
    ?>
</head>

<body>

  <!-- Main Content -->
  <main>
    <div class="sections">

      <!-- Navigation Section - Removed -->
      <div class="button-section">
       </div>

      <!-- Text Section -->
      <div class="text-section">
        <h1 class="main-text">Delete Lease</h1>
        <p class="secondary-text">
          Deleting a lease is perminant and can not be undone.
        </p>
        
        <?php if (isset($db_notice) && $db_notice): ?>
            <div class="alert <?php echo ($pdo ? 'alert-success' : 'alert-error'); ?>">
                <?php echo $db_notice; ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form action="" method="POST">
                <div class="form-row">

                <div style="margin-top: 30px;">
                    <button type="submit" name="delete" class="blue-button">Delete Lease</button>
                </div>
                <?php
                    if (isset($_POST['delete'])) {
                        delete_lease($lease_id);
                        header("Location: leaseView.php");
                        exit();
                    }
                ?>
                
                <div style="margin-top: 30px;">
                    <a href="leaseView.php" class="gray-button">Return to Lease List</a>
                </div>
            </form>
        </div>
      </div>
    </div>
  </main>
</body>
</html>