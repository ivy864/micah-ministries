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
<head>     <link rel="icon" type="image/png" href="images/micah-favicon.png">

    <title>Micah Ministries | View Leases</title>
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
            <h1>List of Leases</h1>
        </div>
    </div>

    <main>
        <div class="main-content-box p-6">

            <div class="d-flex justify-content-end mb-4">
                <!-- Link to add lease form -->
                <a href="addLease.php" class="blue-button">Add a Lease</a>
            </div>

            <!-- editLease.php -->
            <div class="overflow-x-auto">

                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Address</th>
                            <th>Unit Number</th>
                            <th>Program Type</th>
                            <th>Expiration Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Ryan Reynolds</td>
                            <td>123 Free Guy St.</td>
                            <td>23</td>
                            <td>Program #1</td>
                            <td>10/12/25</td>
                            <td>
                                <!-- This will be more concise in something like a for loop when backend is implemented -->
                                <a href="editLease.php" class="return-button">Edit</a>
                                <a href="index.php" class="delete-button">Delete</a>
                            </td>
                        </tr>
                        <tr>
                            <td>Moth Man</td>
                            <td>456 Forest Road</td>
                            <td>76</td>
                            <td>Program #2</td>
                            <td>2/13/26</td>
                            <td>
                                <!-- This will be more concise in something like a for loop when backend is implemented -->
                                <a href="editLease.php" class="return-button">Edit</a>
                                <a href="index.php" class="delete-button">Delete</a>
                            </td>
                        </tr>
                        <tr>
                            <td>Tony Stark</td>
                            <td>789 Avengers Tower</td>
                            <td>54</td>
                            <td>Program #1</td>
                            <td>6/4/27</td>
                            <td>
                                <!-- This will be more concise in something like a for loop when backend is implemented -->
                                <a href="editLease.php" class="return-button">Edit</a>
                                <a href="index.php" class="delete-button">Delete</a>
                            </td>
                        </tr>
                        <!-- Look at checkedInVolunteers.php for ideas on how to implement the backend -->
                    </tbody>
                </table>
            </div>

            <div class="mt-10 flex justify-end">
                <a href="micahportal.php" class="blue-button">Return to Dashboard</a>
            </div>

        </div>
</body>