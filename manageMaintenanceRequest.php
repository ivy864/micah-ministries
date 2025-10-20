<?php
    // Template for new VMS pages. Base your new page on this one

    // Make session information accessible, allowing us to associate
    // data with the logged-in user.
    session_cache_expire(30);
    session_start();

    $loggedIn = false;
    $accessLevel = 0;
    $userID = null;
    if (isset($_SESSION['_id'])) {
        $loggedIn = true;
        // 0 = not logged in, 1 = standard user, 2 = manager (Admin), 3 super admin (TBI)
        $accessLevel = $_SESSION['access_level'];
        $userID = $_SESSION['_id'];
    }

    require_once('domain/MaintenanceRequest.php')
?>
<!DOCTYPE html>
<html>
    <head>
        <?php require_once('universal.inc') ?>
        <title>Micah Ministries | Manage Maintenance Request</title>
        <link href="css/normal_tw.css" rel="stylesheet">

        <style>
            .manage-maintenance-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin: .5rem;
            }
            .manage-maintenance-header > button {
                width: initial;
            }
            td {
                padding: .5rem;
            }
        </style>
    </head>
    <body>
    
        <?php require_once('header.php') ?>
        <main>
            <div class="main-content-box  p-8 w-full max-w-3xl">
                <div class="manage-maintenance-header">
                    <h2>Manage Maintenance Request</h2>
                    <button>Mark Complete</button>
                </div>

                <?php
                    $testdata = [
                        new MaintenanceRequest(0, 'joe biden', 'sink is leaking', '1600 pennsylvania ave', 'bathroom 4', 'plumbing'),
                        new MaintenanceRequest(1, 'crow boy', 'ran out of plastic to destroy', 'big tree', 'branch #39', 'misc'),
                        new MaintenanceRequest(2, 'Tony Hawk', 'skateboard broke', 'the skate park', 'vert ramp', 'carpentry')
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
            </div>
        </main>
    </body>
</html>
