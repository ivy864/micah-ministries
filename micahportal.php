<?php
session_cache_expire(30);
session_start();

$loggedIn   = isset($_SESSION['_id']);
$accessLevel = $loggedIn ? ($_SESSION['access_level'] ?? 0) : 0;
$userID      = $loggedIn ? $_SESSION['_id'] : null;

// Require login (you can tighten this to an accessLevel check if needed)
if (!$loggedIn) {
    header('Location: index.php');
    die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>     <link rel="icon" type="image/png" href="images/micah-favicon.png">

    <title>Micah Ministries | Portal</title>
    <link href="css/normal_tw.css" rel="stylesheet">

    <!-- Match leaseView.php header behavior -->
    <?php
    $tailwind_mode = true;
    require_once('header.php');
    ?>
    <style>
        /* Light portal-specific helpers that align with site look */
        .portal-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
        }
        @media (min-width: 768px) {
            .portal-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        .portal-card {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            transition: box-shadow .15s ease, transform .15s ease;
        }
        .portal-card:hover {
            box-shadow: 0 6px 18px rgba(0,0,0,.08);
            transform: translateY(-2px);
        }
        .portal-title {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
            margin: 0;
        }
        .portal-desc {
            font-size: 14px;
            color: #4b5563;
            margin: 0 0 8px 0;
        }
        .portal-action {
            margin-top: auto;
        }
        /* Reuse site button style if available; fall back to a blue primary */
        .blue-button, .portal-link {
            display: inline-block;
            text-align: center;
            background: #274471;
            color: #fff;
            padding: 10px 16px;
            border-radius: 8px;
            font-weight: 700;
            text-decoration: none;
        }
        .blue-button:hover, .portal-link:hover {
            filter: brightness(1.05);
        }
    </style>
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
            <div class="portal-grid">

                <!-- Lease Management -->
                <div class="portal-card">
                    <h2 class="portal-title">Lease Management</h2>
                    <p class="portal-desc">View and manage leases across programs and units.</p>
                    <div class="portal-action">
                        <!-- Point directly to the teammate’s page -->
                        <a href="leaseView.php" class="portal-link">Go to Lease Management</a>
                    </div>
                </div>

                <!-- Maintenance Management -->
                <div class="portal-card">
                    <h2 class="portal-title">Maintenance Management</h2>
                    <p class="portal-desc">Create, assign, and track maintenance requests.</p>
                    <div class="portal-action">
                        <a href="maintman.php" class="portal-link">Go to Maintenance</a>
                    </div>
                </div>

                <!-- Edit Profile -->
                <div class="portal-card">
                    <h2 class="portal-title">Manage Users</h2>
                    <p class="portal-desc">Update account details for users.</p>
                    <div class="portal-action">
                        <a href="userman.php" class="portal-link">Manage Users</a>
                    </div>
                </div>

            </div>
        </div>
    </main>
</body>
</html>
