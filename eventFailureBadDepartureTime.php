<?php
    session_cache_expire(30);
    session_start();
    header("refresh:2; url=index.php"); 
?>
    <!DOCTYPE html>
    <html>
        <head>     <link rel="icon" type="image/png" href="images/micah-favicon.png">

            <?php require_once('universal.inc') ?>
            <title>Fredericksburg SPCA | Sign-Up for Event</title>
        </head>
        <body>
            <?php require_once('header.php') ?>
            <h1>Oops! You tried to leave earlier than you arrive.</h1>
        </body>
    </html>