<?php
    require_once('include/input-validation.php');
?>

<!DOCTYPE html>
<html>
<head>
    <?php require_once('database/dbMessages.php'); ?>
    <title>Mobility Options | Add Vehicle</title>
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
    require_once('database/dbEvents.php');

    $errors = [];
    $args = [];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $args = sanitize($_POST);

        $required = ['plate', 'vin', 'capacity'];

        if (!wereRequiredFieldsSubmitted($args, $required)) {
            $errors[] = 'Please fill in all required fields.';
        }

        $plate    = trim($args['plate']);
        $vin      = trim($args['vin']);
        $capacity = (int) $args['capacity'];
        $wheelchair_accessible = isset($args['wheelchair_accessible']) ? 1 : 0;
        $make_model = trim($args['make_model'] ?? '');
        $notes      = trim($args['notes'] ?? '');

        if (empty($errors) && $capacity <= 0) {
            $errors[] = 'Capacity must be a positive number.';
        }

        if (empty($errors)) {
            $result = add_vehicle($plate, $vin, $capacity, $wheelchair_accessible, $make_model, $notes);
            if ($result === false) {
                $errors[] = 'Could not add vehicle. Please try again.';
            } else {
                $success = ('Added Vehicle!');
            }
        }
    }
?>

<!-- Hero Section with Title -->
<header class="hero-header">
    <div class="center-header">
        <h1>Add Vehicle</h1>
    </div>
</header>

<main>
    <div class="main-content-box">
        <?php if (!empty($errors)): ?>
            <div class="info-box" style="border-left: 4px solid red; margin-bottom: 1rem;">
                <?php foreach ($errors as $error): ?>
                    <p style="color: red;"><?= ($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="info-box" style="border-left: 4px solid green; margin-bottom: 1rem;">
                <p style="color: green;"><?= $success ?></p>
            </div>
            <script>setTimeout(() => { document.location = "driverVehicleManagement.php"; }, 2000);</script>
        <?php endif; ?>

        <form class="signup-form" method="POST" action="addVehicles.php">
            <div class="text-center spacing-bottom">
                <h2 class="mb-8">Vehicle Registration Form</h2>
                <div class="info-box">
                    <p class="sub-text">Fill out the form below to add a new vehicle to the system.</p>
                    <p>An asterisk ( <em>*</em> ) indicates a required field.</p>
                </div>
            </div>

            <fieldset class="section-box mb-4">
                <h3 class="mt-2">Vehicle Information</h3>
                <p class="mb-2">The following information will be used to create a vehicle record.</p>
                <div class="blue-div"></div>

                <label for="plate"><em>* </em>License Plate</label>
                <input type="text" id="plate" name="plate" required placeholder="Enter license plate"
                       value="<?= ($args['plate'] ?? '') ?>">

                <label for="vin"><em>* </em>VIN</label>
                <input type="text" id="vin" name="vin" required placeholder="Enter VIN"
                       value="<?= ($args['vin'] ?? '') ?>">

                <label for="capacity"><em>* </em>Capacity</label>
                <input type="number" id="capacity" name="capacity" required min="1" placeholder="Enter passenger capacity"
                       value="<?= ($args['capacity'] ?? '') ?>">

                <label for="make_model">Make / Model</label>
                <input type="text" id="make_model" name="make_model" placeholder="e.g. Ford Transit 2022"
                       value="<?= ($args['make_model'] ?? '') ?>">

                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" placeholder="Any additional notes about the vehicle"><?= ($args['notes'] ?? '') ?></textarea>

                <div class="radio-group" style="margin-top: 10px;">
                    <div class="radio-element">
                        <input type="checkbox" id="wheelchair_accessible" name="wheelchair_accessible" value="1"
                               <?= !empty($args['wheelchair_accessible']) ? 'checked' : '' ?>>
                        <label for="wheelchair_accessible">Wheelchair Accessible</label>
                    </div>
                </div>
            </fieldset>

            <div class="text-center mt-4">
                <input type="submit" value="Add Vehicle">
            </div>
        </form>

          <div class="text-center mt-6">
        <a href="driverVehicleManagement.php" class="return-button">Return to Driver &amp; Vehicle Management</a>
        </div>
    </div>
</main>

</body>
</html>
