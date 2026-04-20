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
    <title>Mobility Options | All Riders</title>
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

        body, main {
        background-color: #fafafa;
        }

        .text-blue-700,
        .text-blue-700:visited {
        color: #666 !important;
        }   

        /* .info-section .info-text {
         color: #666 !important;
        } */

        .blue-div {
        background-color: #fafafa !important;
        }

        .main-content-box label {
        color: #000000 !important;
        }
        

        .text-blue-700,
        .text-blue-700:visited,
        .text-blue-700:hover {
            color: black !important;
        }
        
        .sub-text {
        color: #666 !important;
        }
 
        /* .main-content-box table,
        .main-content-box table thead,
        .main-content-box table tbody,
        .main-content-box table tr,
        .main-content-box table th, */
        /* .main-content-box table td {
            background-color: #fafafa !important;
            color: #C9AB81 !important;
            border: 1px solid #45892e !important;
        }

        .main-content-box table a.text-blue-700,
        .main-content-box table a.text-blue-700:visited {
            color: #C9AB81 !important;
            }

        
       

    .return-button:hover,
    .return-button:visited,
    .return-button:focus {
        color: #ffffff !important;
    }

    .return-button:hover {
        background-color: #963737;
    }

        .main-content-box table thead.bg-blue-400 th {
            background-color: #1F1F21 !important;
        }

        .main-content-box table a.text-blue-700,
        .main-content-box table a.text-blue-700:visited {
            color: #C9AB81 !important;
        } */

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

    .return-button:hover,
    .return-button:visited,
    .return-button:focus {
        color: #ffffff !important;
    }

    .return-button:hover {
        background-color: #963737;
    }
    
</style>
<!-- BANDAID END, REMOVE ONCE SOME GENIUS FIXES -->
</head>
<body>

<header class="hero-header">
    <div class="center-header">
        <h1>Manage Drivers</h1>
    </div>
</header>

<main>
    <div class="main-content-box w-[80%] p-8">

        <?php
            require_once('include/input-validation.php');
            require_once('database/dbPersons.php');

            $deleteError = null;
            $deleteSuccess = null;

            if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['delete_id'])) {
                $delete_id = sanitize($_POST)['delete_id'];
                $ok = delete_driver($delete_id);
                if ($ok) {
                    $deleteSuccess = 'Driver has been removed successfully.';
                } else {
                    $deleteError = 'Could not delete driver. They may have trips assigned.';
                }
            }

            if ($deleteSuccess): ?>
                <div class="info-box" style="border-left:4px solid green; margin-bottom:1rem;">
                    <p style="color:green;"><?= $deleteSuccess ?></p>
                </div>
            <?php endif; ?>

            <?php if ($deleteError): ?>
                <div class="info-box" style="border-left:4px solid red; margin-bottom:1rem;">
                    <p style="color:red;"><?= $deleteError ?></p>
                </div>
            <?php endif; ?>

        <?php
            $drivers = get_drivers_with_email();

            if (count($drivers) > 0):
        ?>
        <div class="overflow-x-auto">
            <table>
                <thead class="bg-blue-400">
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($drivers as $driver): ?>
                    <tr>
                        <td><?= $driver['first_name'] ?></td>
                        <td><?= $driver['last_name'] ?></td>
                        <td>
                            <a href="mailto:<?= $driver['email'] ?>" class="text-blue-700 underline">
                                <?= $driver['email'] ?? '—' ?>
                            </a>
                        </td>
                        <td>
                            <form method="POST" action="manageDrivers.php" style="display:inline;">
                                <input type="hidden" name="delete_id" value="<?= $driver['id'] ?>">
                                <button type="submit"
                                    onclick="return confirm('Are you sure you want to delete <?= ($driver['first_name'] . ' ' . $driver['last_name']) ?>? This cannot be undone.')"
                                    style="color:red; background:none; border:none; cursor:pointer; text-decoration:underline;">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="error-block">No drivers found.</div>
        <?php endif; ?>

    </div>

    <div class="text-center mt-6">
        <a href="driverVehicleManagement.php" class="return-button">Return to Driver &amp; Vehicle Management</a>
    </div>

    <div class="info-section">
        <div class="blue-div"></div>
    </div>
</main>

</body>
</html>