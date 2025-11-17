<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    session_cache_expire(30);
    session_start();

    date_default_timezone_set("America/New_York");
    
    if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] < 1) {
        if (isset($_SESSION['change-password'])) {
            header('Location: changePassword.php');
        } else {
            header('Location: login.php');
        }
        die();
    }
        
    include_once('database/dbPersons.php');
    include_once('domain/Person.php');
    // Get date?
    if (isset($_SESSION['_id'])) {
        $person = retrieve_person($_SESSION['_id']);
    }
    $notRoot = $person->get_id() != 'vmsroot';
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

<!-- ONLY SUPER ADMIN WILL SEE THIS -->
<?php if ($_SESSION['access_level'] >= 2): ?>
<body>
<?php require 'header.php';?>

    <!-- Dummy content to enable scrolling -->
    <div style="margin-top: 0px; padding: 30px 20px;">
        <h2><b>Welcome <?php echo $person->get_first_name() ?>!</b> Let's get started.</h2>
    </div>

            <?php if (isset($_GET['pcSuccess'])): ?>
                <div class="happy-toast">Password changed successfully!</div>
            <?php elseif (isset($_GET['deleteService'])): ?>
                <div class="happy-toast">Service successfully removed!</div>
            <?php elseif (isset($_GET['serviceAdded'])): ?>
                <div class="happy-toast">Service successfully added!</div>
            <?php elseif (isset($_GET['animalRemoved'])): ?>
                <div class="happy-toast">Animal successfully removed!</div>
            <?php elseif (isset($_GET['locationAdded'])): ?>
                <div class="happy-toast">Location successfully added!</div>
            <?php elseif (isset($_GET['deleteLocation'])): ?>
                <div class="happy-toast">Location successfully removed!</div>
            <?php elseif (isset($_GET['registerSuccess'])): ?>
                <div class="happy-toast">Volunteer registered successfully!</div>
            <?php endif ?>

    <div class="full-width-bar">
    <div class="content-box">
        <img src="images/VolM.png" />
        <div class="small-text">Make a difference.</div>
        <div class="large-text">Volunteer Management</div>
<button class="circle-arrow-button" onclick="window.location.href='volunteerManagement.php'">
    <span class="button-text">Go</span>
    <div class="circle">&gt;</div>
</button>
<!--
        <div class="nav-buttons">
            <button class="nav-button" onclick="window.location.href='personSearch.php'">
                <span>Find</span>
                <span class="arrow"><img src="images/person-search.svg" style="width: 40px; border-radius:5px; border-bottom-right-radius: 20px;"></span>
            </button>
            <button class="nav-button" onclick="window.location.href='VolunteerRegister.php'">
                <span>Register</span>
                <span class="arrow"><img src="images/add-person.svg" style="width: 40px; border-radius:5px; border-bottom-right-radius: 20px;"></span>
            </button>
        </div>
-->
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

                <!-- Profile Management - Level 1 and 2 (maintenance staff and case managers) -->
                <?php if ($accessLevel == 1 || $accessLevel == 2): ?>
                <div class="portal-card">
                    <h2 class="portal-title">Manage Profile</h2>
                    <p class="portal-desc">Update your account information and preferences.</p>
                    <div class="portal-action">
                        <a href="editProfile.php" class="portal-link">Edit My Profile</a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- User Management - Level 3 only (Admin) -->
                <?php if ($accessLevel >= 3): ?>
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
<?php endif ?>
</html>
