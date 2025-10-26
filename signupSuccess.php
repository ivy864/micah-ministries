<?php
    session_cache_expire(30);
    session_start();
    header("refresh:2; url=viewAllEvents.php");
?>
    <!DOCTYPE html>
    <html>
        <head>     <link rel="icon" type="image/png" href="images/micah-favicon.png">

            <?php require_once('universal.inc') ?>
            <title>Fredericksburg SPCA | Sign-Up for Event</title>
        </head>
        <body>
            <?php require_once('header.php') ?>
            <h1>Sign-Up Approved!</h1>
        </body>
    </html>