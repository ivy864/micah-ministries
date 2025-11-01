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

    <title>Micah Ministries | Delete Lease</title>
    <link href="css/management_tw.css?v=<?php echo time(); ?>" rel="stylesheet">
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
        
        .form-container {
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            background-color: white;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #274471;
            box-shadow: 0 0 0 2px rgba(39, 68, 113, 0.1);
        }
        
        .form-row {
            display: flex;
            gap: 20px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .btn-primary {
            background-color: #274471;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-right: 10px;
        }
        
        .btn-primary:hover {
            background-color: #1e3554;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .text-section h1 {
            margin-bottom: 5px !important;
            color: #274471 !important;
            font-size: 28px !important;
            font-weight: 700 !important;
        }
        
        .text-section p {
            margin-bottom: 10px !important;
            color: #666 !important;
            font-size: 16px !important;
        }
        
        .sections {
            flex-direction: row !important;
            gap: 10px !important;
        }
        
        main {
            margin-top: 0 !important;
            padding: 10px !important;
        }
        
        .button-section {
            width: 0% !important;
            display: none !important;
        }
        
        .text-section {
            width: 100% !important;
        }
        
        .form-container {
            max-width: none !important;
            width: 100% !important;
        }
    </style>
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
        <h1>Delete Lease</h1>
        <div class="div-blue"></div>
        <p>
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
                    <button type="submit" name="delete" class="btn-primary">Delete Lease</button>
                </div>
                <?php
                    if (isset($_POST['delete'])) {
                        delete_lease($lease_id);
                        header("Location: leaseView.php");
                        exit();
                    }
                ?>
                
                <div style="margin-top: 30px;">
                    <a href="leaseView.php" class="btn-secondary">Return to Lease List</a>
                </div>
            </form>
        </div>
      </div>
    </div>
  </main>
</body>
</html>