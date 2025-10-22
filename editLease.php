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
include_once "database/dbPersons.php";
include_once "database/dbShifts.php";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Micah Ministries | Add Leases</title>
  	<link href="css/normal_tw.css" rel="stylesheet">

    <!-- BANDAID FIX FOR HEADER BEING WEIRD -->
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
            .dropdown {
                padding-right: 50px;
            }

    </style>
    <!-- BANDAID END, REMOVE ONCE SOME GENIUS FIXES -->
</head>
<body>
    <div class="hero-header">
        <div class="center-header">
            <h1>Add a Lease</h1>
        </div>
    </div>

    <main>
        <div class="main-content-box p-6">

            <!-- WIP -->
            <!-- Need to format so that it looks better -->
            <div class="overflow-x-auto">
                <form action="processAddLease.php" method="POST" class="space-y-4">
                    <div>
                        <label for="tenant_name" class="block font-medium text-gray-700">Tenant Name:</label>
                        <input type="text" id="tenant_name" name="tenant_name" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                    </div>

                    <div>
                        <label for="property_address" class="block font-medium text-gray-700">Property Address:</label>
                        <input type="text" id="property_address" name="property_address" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                    </div>

                    <div>
                        <label for="unit_number" class="block font-medium text-gray-700">Unit Number:</label>
                        <input type="text" id="unit_number" name="unit_number" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                    </div>

                    <div>
                        <label for="lease_start_date" class="block font-medium text-gray-700">Lease Start Date:</label>
                        <input type="date" id="lease_start_date" name="lease_start_date" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                    </div>

                    <div>
                        <label for="lease_end_date" class="block font-medium text-gray-700">Lease End Date:</label>
                        <input type="date" id="lease_end_date" name="lease_end_date" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                    </div>

                    <div>
                        <label for="rent_amount" class="block font-medium text-gray-700">Rent Amount:</label>
                        <input type="number" step="0.01" id="rent_amount" name="rent_amount" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                    </div>

                    <div>
                        <button type="submit" class="blue-button">Sumbit Changes</button>
                    </div>
                </form>

                <div class="d-flex justify-content-end mb-4">
                    <a href="micahportal.php" class="blue-button">Return to Dashboard</a>
                </div>

            </div>
        </div>
</body>