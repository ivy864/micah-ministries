<!DOCTYPE html>
<html>
<head>
    <?php require_once('database/dbMessages.php'); ?>
    <title>Micah Ministries | Manage Maintenance Request</title>
    <link href="css/normal_tw.css" rel="stylesheet">
<!-- BANDAID FIX FOR HEADER BEING WEIRD -->
<?php
$tailwind_mode = true;
require_once('header.php');
?>

<body class="relative">
<?php
    require_once('domain/MaintenanceRequest.php')
?>

<style>
    .manage-maintenance-header {
        display: flex;
        justify-content: space-between;
    }
    .manage-maintenance-header > button {
        margin-right: 5em;
        background-color: #5C91B6;
        color: white;
        padding: .5em;
        border-radius: 1em;
        font-weight: bold;
    }

    .manage-maintenance-header > button:hover {
        background-color: #4E7FA2;
    }
</style>

<div class="manage-maintenance-header">
    <h2>Manage Maintenance Request</h2>
    <button>Mark Complete</button>
</div>


<?php
    $testdata = [
        new MaintenanceRequest(0, 'joe biden', 'sink is leaking', '1600 pennsylvania ave', 'bathroom 4'),
        new MaintenanceRequest(1, 'crow boy', 'ran out of plastic to destroy', 'big tree', 'branch #39'),
        new MaintenanceRequest(2, 'Tony Hawk', 'skateboard broke', 'the skate park', 'vert ramp')
    ];

    $request = $testdata[$_GET['id']];
?>

<table>
    <tr>
        <td>Name:</td>
        <td><?php echo $request->getRequester(); ?></td>
    </tr>    
    <tr>
        <td>Address:</td>
        <td><?php echo $request->getAddress(); ?></td>
    </tr>
    <tr>
        <td>Area:</td>
        <td><?php echo $request->getArea(); ?></td>
    </tr>
    <tr>
        <td>Description:</td>
        <td><?php echo $request->getDesc(); ?></td>
    </tr>
</table>

</body>
