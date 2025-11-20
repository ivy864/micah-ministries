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

    <meta HTTP-EQUIV="REFRESH" content="2; url=index.php">
    <title>Logged Out | Micah Ecumenical Ministries</title>
    <link rel="icon" type="image/png" href="images/micah-favicon.png">
    <?php require('universal.inc') ?>
</head>

    <body>
        <div style="padding: 30px; text-align: center;">
            <img src="images\micah-ministries-logo.jpg" alt="Micah Ministries Logo">
        </div>
        <p class="main-text">You've successfully been logged out</p>
    </body>
</html>
