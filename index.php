<?php
session_cache_expire(30);
session_start();

$loggedIn   = isset($_SESSION['_id']);
$accessLevel = $loggedIn ? ($_SESSION['access_level'] ?? 0) : 0;
$userID      = $loggedIn ? $_SESSION['_id'] : null;

// Require login (you can tighten this to an accessLevel check if needed)
if (!$loggedIn) {
    header('Location: login.php');
    die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>     <link rel="icon" type="image/png" href="images/micah-favicon.png">

    <title>Micah Ministries | Portal</title>
    <link href="css/base.css" rel="stylesheet">
    <link href="css/dashboard.css" rel="stylesheet">

    <!-- Match leaseView.php header behavior -->
    <?php
    $tailwind_mode = true;
    require_once('header.php');
    ?>
</head>
    
<link rel="icon" type="image/png" href="images/micah-favicon.png">

<body>
    <div class="hero-header">
        <div class="center-header">
            <h1>Micah Ministries Management Portal</h1>
        </div>
    </div>

    <main>
        <div class="main-content-box p-6">
            <div class="portal-grid <?php echo ($accessLevel == 1) ? 'portal-grid-two-cards' : ''; ?>">

                <!-- Lease Management - Level 2+ only -->
                <?php if ($accessLevel >= 2): ?>
                <div class="portal-card">
                    <h2 class="portal-title">Lease Management</h2>
                    <p class="portal-desc">View and manage leases across programs and units.</p>
                    <div class="portal-action">
                        <a href="leaseman.php" class="portal-link">Go to Lease Management</a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Maintenance Management - All logged in users -->
                <div class="portal-card">
                    <h2 class="portal-title">Maintenance Management</h2>
                    <p class="portal-desc">Create, assign, and track maintenance requests.</p>
                    <div class="portal-action">
                        <a href="maintman.php" class="portal-link">Go to Maintenance</a>
                    </div>
                </div>

                <!-- Profile Management - Level 1 only (maintenance staff) -->
                <?php if ($accessLevel == 1): ?>
                <div class="portal-card">
                    <h2 class="portal-title">Manage Profile</h2>
                    <p class="portal-desc">Update your account information and preferences.</p>
                    <div class="portal-action">
                        <a href="editProfile.php" class="portal-link">Edit My Profile</a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- User Management - Level 2+ only -->
                <?php if ($accessLevel >= 2): ?>
                <div class="portal-card">
                    <h2 class="portal-title">Manage Users</h2>
                    <p class="portal-desc">Update account details for users.</p>
                    <div class="portal-action">
                        <a href="userman.php" class="portal-link">Manage Users</a>
                    </div>
                </div>
                <?php endif; ?>


            </div>
        </div>
    </main>
</body>
</html>
