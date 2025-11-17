<?php
session_cache_expire(30);
session_start();

// Clear and destroy session
session_unset();
session_destroy();
session_write_close();
?>
<html>
    <head>     <link rel="icon" type="image/png" href="images/micah-favicon.png">

    <meta HTTP-EQUIV="REFRESH" content="2; url=micahportal.php">
    <title>Logged Out | Micah Ecumenical Ministries</title>
    <link rel="icon" type="image/png" href="images/micah-favicon.png">
    <?php require('universal.inc') ?>
</head>

    <body>
        <nav>
            <span id="nav-top">
                <span class="logo">
                    <img src="images/micah-ministries-logo.jpg">
                        <span id="vms-logo"> Micah Ecumenical Ministries </span>
                        </span>
                    <img id="menu-toggle" src="images/menu.png">
                </span>
            </span>
        </nav>
        <main>
            <p class="happy-toast centered">You have been logged out.</p>
        </main>
    </body>
</html>
