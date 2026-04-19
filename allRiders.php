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

        .main-content-box table thead.bg-blue-400 th {
            background-color: #1F1F21 !important;
        }

        .main-content-box table a.text-blue-700,
        .main-content-box table a.text-blue-700:visited {
            color: #C9AB81 !important;
        } */
    
</style>
<!-- BANDAID END, REMOVE ONCE SOME GENIUS FIXES -->
</head>
<body>

<header class="hero-header">
    <div class="center-header">
        <h1>All Riders</h1>
    </div>
</header>

<main>
    <div class="main-content-box w-[80%] p-8">

        

        <?php
            require_once('include/input-validation.php');
            require_once('database/dbPersons.php');
            require_once('include/output.php');

            $persons = getall_persons();

            if (count($persons) > 0) {
                echo '
                <div class="overflow-x-auto">
                    <table>
                        <thead class="bg-blue-400">
                            <tr>
                                <th>First</th>
                                <th>Last</th>
                                <th>Username</th>
                                <th>Phone</th>
                                <th>Zip Code</th>
                                <th>Update Eligibility</th>
                               
                            </tr>
                        </thead>
                        <tbody>';
                $mailingList = '';
                $notFirst = false;
                foreach ($persons as $person) {
                    if ($notFirst) {
                        $mailingList .= ', ';
                    } else {
                        $notFirst = true;
                    }
                    $mailingList .= $person->get_email();
                    echo '
                            <tr>
                                <td>' . $person->get_first_name() . '</td>
                                <td>' . $person->get_last_name() . '</td>
                                <td><a href="mailto:' . $person->get_id() . '" class="text-blue-700 underline">' . $person->get_id() . '</a></td>
                                <td><a href="tel:' . $person->get_phone1() . '" class="text-blue-700 underline">' . formatPhoneNumber($person->get_phone1()) . '</a></td>
                                <td>' . $person->get_zip_code() . '</td>
                                <td><a href="viewProfile.php?id=' . $person->get_id() . '" class="text-blue-700 underline">Profile</a></td>
                             
                            </tr>';
                }
                echo '
                        </tbody>
                    </table>
                </div>';

            } else {
                echo '<div class="error-block">No riders found.</div>';
            }
        ?>

    </div>

    <div class="text-center mt-6">
        <a href="volunteerManagement.php" class="return-button">Return to Rider Management</a>
    </div>

    <div class="info-section">
        <div class="blue-div"></div>
        
    </div>
</main>

</body>
</html>