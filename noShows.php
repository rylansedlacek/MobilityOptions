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

// admin-only access
if ($accessLevel < 2) {
    header('Location: index.php');
    die();
}

require_once 'database/dbPersons.php';
require_once 'domain/Person.php';
require_once 'database/dbEvents.php';
require_once 'domain/Event.php';


?>

<!DOCTYPE html>
<html lang="en">
<head>
  	<link href="css/normal_tw.css" rel="stylesheet">
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


        body, main{
            background-color: #ffffff;
        }

        .main-content-box table,
        .main-content-box table tbody,
        .main-content-box table tr,
        .main-content-box table td {
        background-color: #ffffff !important;
        color: #000000 !important;
        border: 1px solid #ccc !important;
}

        .main-content-box table thead {
            background-color: #4299e1;
            color: #ffffff;
        }

        .return-button {
        display: inline-block;
        width: 94%;
        color: #ffffff !important;
        background-color: #b44444;
        padding: 0.5rem 1.5rem;
        border: 3px solid rgba(255, 255, 255, 0.295);
        border-radius: 3rem;
        font-weight: 500;
        text-align: center;
        text-decoration: none;
        transition: background-color .3s;
        cursor: pointer;
    }




</style>
<!-- BANDAID END, REMOVE ONCE SOME GENIUS FIXES -->

</head>
<body>
    <header class="hero-header">
        <div class="center-header">
            <h1>No Shows</h1>
        </div>
    </header>

    <main>
        <div class="main-content-box w-[80%] p-6">
            <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>

            <table>
                <thead class="bg-blue-400 text-white">
                    <tr>
                        <th>Username</th>
                        <th>Name</th>
                        <th>Number of No Shows</th>
                        <th>Edit Profile</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($no_show_riders)): ?>
                        <?php foreach ($no_show_riders as $rider): ?>
                            <?php
                                $userID = $rider->get_id();
                                $name = $rider->get_first_name() . ' ' . $rider->get_last_name();
                                $no_sho = isset($no_show_counts[$userID]) ? $no_show_counts[$userID] : 0;
                                $profile_link = "viewProfile.php?id=" . urlencode($userID);
                            ?>
                            <tr>
                                <td><?= $userID ?></td>
                                <td><?= ($name) ?></td>
                                <td><?= ($no_sho) ?></td>
                                <td><a href="<?= $profile_link ?>" class="button">Edit</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4">No riders with no-shows found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>



        </div>
        <div class="text-center mt-4">
                <a href="volunteerManagement.php" class="return-button">Return to Rider Management</a>
            </div>

        <div class="info-section">
            <div class="blue-div"></div>
            <p class="info-text">
            </p>
        </div>
    </main>
</body>
</html>
