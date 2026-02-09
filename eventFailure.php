<?php
    session_cache_expire(30);
    session_start();
    header("refresh:2; url=viewAllEvents.php");
?>
    <!DOCTYPE html>
    <html>
        <head>
            <?php require_once('universal.inc') ?>
            <title>Whiskey Valor | Sign-Up for Event</title>
            <style>
                .h1 {
                        font-family: Quicksand, sans-serif;
                        color: #white;
                        font-weight: normal;
                        font-size: 30px;
                    }
            </style>
        </head>
        <body>
            <?php require_once('header.php') ?>
            <h1>Oops! You are already signed up for this event.</h1>
        </body>
    </html>

    <?php
    exit();