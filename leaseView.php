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
    
  <link rel="icon" type="image/png" href="images/micah-favicon.png">
  <link href="css/base.css?v=<?php echo time(); ?>" rel="stylesheet">

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View All Leases</title>
 

<!-- BANDAID FIX FOR HEADER BEING WEIRD -->
<?php
$tailwind_mode = true;
require_once('header.php');
?>

</head>
<body>
    <div class="hero-header">
        <div class="center-header">
            <h1>List of Leases</h1>
        </div>
    </div>

    <main>
        <div class="main-content-box p-6">
          <p class="secondary-text" style="margin-bottom: 10px; text-align: left;">
            View and manage all leases. Use the table below to see lease details, tenant information, and property details.
          </p>

          <!-- NEW: Month filter (adds on top of existing features; preserves look) -->
          <form class="filter-bar" method="get">
            <!-- preserve current sort when filtering -->
            <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort_by, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="order" value="<?php echo htmlspecialchars($sort_order, ENT_QUOTES, 'UTF-8'); ?>">
            <!-- when applying a filter, start at page 1 -->
            <input type="hidden" name="page" value="1">

            <div class="d-flex justify-content-end mb-4">
                <!-- Replace index.php with the add page for leases -->
                <a href="index.php" class="blue-button">Add a Lease</a>
            </div>

            <!-- editLease.php -->
            <div class="overflow-x-auto">
                <table class="sortable-table">
                <thead>
                    <tr>
                        <th><a href="<?php echo getSortUrl('id'); ?>" style="color: #274471; text-decoration: none;">Lease ID<?php echo getSortArrow('id'); ?></a></th>
                        <th><a href="<?php echo getSortUrl('tenant_first_name'); ?>" style="color: #274471; text-decoration: none;">Tenant Name<?php echo getSortArrow('tenant_first_name'); ?></a></th>
                        <th><a href="<?php echo getSortUrl('property_street'); ?>" style="color: #274471; text-decoration: none;">Property Address<?php echo getSortArrow('property_street'); ?></a></th>
                        <th><a href="<?php echo getSortUrl('unit_number'); ?>" style="color: #274471; text-decoration: none;">Unit<?php echo getSortArrow('unit_number'); ?></a></th>
                        <th><a href="<?php echo getSortUrl('program_type'); ?>" style="color: #274471; text-decoration: none;">Program Type<?php echo getSortArrow('program_type'); ?></a></th>
                        <th><a href="<?php echo getSortUrl('expiration_date'); ?>" style="color: #274471; text-decoration: none;">Expiration Date<?php echo getSortArrow('expiration_date'); ?></a></th>
                        <th><a href="<?php echo getSortUrl('status'); ?>" style="color: #274471; text-decoration: none;">Status<?php echo getSortArrow('status'); ?></a></th>
                        <th><a href="<?php echo getSortUrl('monthly_rent'); ?>" style="color: #274471; text-decoration: none;">Monthly Rent<?php echo getSortArrow('monthly_rent'); ?></a></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leases as $lease): ?>
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