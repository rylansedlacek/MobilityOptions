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
// admin-only access
if ($accessLevel < 2) {
    header('Location: index.php');
    die();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mobility Options | Ride Requests</title>
    <link href="css/management_tw.css" rel="stylesheet">

    <!-- BANDAID FIX FOR HEADER BEING WEIRD -->
    <?php
    $tailwind_mode = true;
    require_once('header.php');
    ?>
    <style>
        .date-box {
            background: #2B2B2E;
            padding: 10px 30px;
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

        body {
            background-color: #fafafa;
        }

        .button-section button {
            background-color: #fafafa;
            color: black !important;
        }

        .button-left-gray {
            background-color: #fafafa;
        }

        .button-section .button-icon {
            filter: none
        }

        .top-bar {
            background-color: #fafafa;
            height: calc(var(--spacing) * 40);
            width: 100%;
            position: relative;
            z-index: 0;
            height: calc(var(--spacing) * 40);
            width: 100%;
            background-size: auto;
            background-position: center;
        }
    </style>

<body>
    <!-- Larger Hero Section -->

    <!-- Main Content -->
   <main style="margin-top: 5px;">
    <div style="display: flex; flex-direction: column; align-items: center; width: 100%;">

      <h1 class="text-section" style="text-align: center; margin-bottom: 1.5rem;">Trip Management</h1>

      <!-- Buttons Section -->
      <div class="button-section" style="max-width: 1000px; width: 100%;">

                <button onclick="window.location.href='viewAllTrips.php';">
                    <div class="button-left-gray"></div>
                    <div>Dispatch Trip</div>
                    <img class="button-icon h-10 w-10 left-5" src="images/dispatchTripVehicleLogo.jpg" alt="Calendar Icon">
                </button>

                <!-- <button onclick="window.location.href='viewAllApplications.php';">
                    <div class="button-left-gray"></div>
                    <div>Track Trip</div>
                    <img class="button-icon h-10 w-10 left-5" src="images/trackTripQuestionMarkLogo.jpg" alt="Calendar Icon">
                </button> -->

                 <button onclick="window.location.href='completeTrips.php';">
                    <div class="button-left-gray"></div>
                    <div>Complete Trip</div>
                    <img class="button-icon h-10 w-10 left-5" src="images/completeTrip.png" alt="Calendar Icon">
                </button>

                 <button onclick="window.location.href='cancelTrips.php';">
                    <div class="button-left-gray"></div>
                    <div>Cancel Trip</div>
                    <img class="button-icon h-12 w-12 left-4" src="images/editTripDirectionLogo.avif" alt="Calendar Icon">
                </button>

                <div class="text-center mt-6">
                    <a href="index.php" class="return-button">Return to Dashboard</a>
                </div>


            </div>

            <!-- Text Section -->
           
            </div>

        </div>
    </main>
</body>

</html>