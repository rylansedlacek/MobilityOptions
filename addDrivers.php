<?php
    require_once('include/input-validation.php');
?>

<!DOCTYPE html>
<html>
<head>
    <?php require_once('database/dbMessages.php'); ?>
    <title>Mobility Options | Add Driver</title>
    <link href="css/base.css" rel="stylesheet">
<!-- BANDAID FIX FOR HEADER BEING WEIRD -->
<?php
$tailwind_mode = true;
require_once('header.php');
?>
<style>
    .date-box {
        background: #C9AB81;
        padding: 7px 30px;
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
</style>
<!-- BANDAID END, REMOVE ONCE SOME GENIUS FIXES -->
</head>
<body class="relative">
<?php
    require_once('database/dbPersons.php');

    $errors = [];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $args = sanitize($_POST);

        $required = ['first_name', 'last_name', 'email'];

        if (!wereRequiredFieldsSubmitted($args, $required)) {
            $errors[] = 'Please fill in all required fields.';
        }

        $first_name = trim($args['first_name']);
        $last_name  = trim($args['last_name']);
        $email      = strtolower(trim($args['email']));

        if (empty($errors) && !validateEmail($email)) {
            $errors[] = 'Invalid email address.';
        }

        if (empty($errors)) {
            $result = add_driver($first_name, $last_name, $email);
            if ($result === false) {
                $errors[] = 'Could not add driver. The record may already exist.';
            } else {
                $success = ($first_name . ' ' . $last_name);
            }
        }
    }
?>

<!-- Hero Section with Title -->
<header class="hero-header">
    <div class="center-header">
        <h1>Add Driver</h1>
    </div>
</header>

<main>
    <div class="main-content-box">
        <?php if (!empty($errors)): ?>
            <div class="info-box" style="border-left: 4px solid red; margin-bottom: 1rem;">
                <?php foreach ($errors as $error): ?>
                    <p style="color: red;"><?= $error ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="info-box" style="border-left: 4px solid green; margin-bottom: 1rem;">
                <p style="color: green;">Driver <strong><?= $success ?></strong> was added successfully.</p>
            </div>
            <script>setTimeout(() => { document.location = "driverVehicleManagement.php"; }, 2000);</script>
        <?php endif; ?>

        <form class="signup-form" method="POST" action="addDrivers.php">
            <div class="text-center spacing-bottom">
                <h2 class="mb-8">Driver Registration Form</h2>
                <div class="info-box">
                    <p class="sub-text">Fill out the form below to add a new driver to the system.</p>
                    <p>An asterisk ( <em>*</em> ) indicates a required field.</p>
                </div>
            </div>

            <fieldset class="section-box mb-4">
                <h3 class="mt-2">Driver Information</h3>
                <p class="mb-2">The following information will be used to create a driver profile.</p>
                <div class="blue-div"></div>

                <label for="first_name"><em>* </em>First Name</label>
                <input type="text" id="first_name" name="first_name" required placeholder="Enter first name"
                       value="<?= isset($args['first_name']) ? $args['first_name'] : '' ?>">

                <label for="last_name"><em>* </em>Last Name</label>
                <input type="text" id="last_name" name="last_name" required placeholder="Enter last name"
                       value="<?= isset($args['last_name']) ? $args['last_name'] : '' ?>">

                <label for="email"><em>* </em>Email</label>
                <input type="email" id="email" name="email" required placeholder="Enter email address"
                       value="<?= isset($args['email']) ? $args['email'] : '' ?>">
            </fieldset>

            <div class="text-center mt-4">
                <input type="submit" value="Add Driver">
            </div>

              <div class="text-center mt-6">
        <a href="driverVehicleManagement.php" class="return-button">Return to Driver &amp; Vehicle Management</a>
        </div>
        </form>
    </div>
</main>

</body>
</html>
