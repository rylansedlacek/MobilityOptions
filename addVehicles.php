<?php
    session_start();
    require_once('include/input-validation.php');
?>

<!DOCTYPE html>
<html>
<head>
    <?php require_once('universal.inc'); ?>
    <title>Mobility Options | Add Vehicle</title>
    <link href="css/base.css" rel="stylesheet">
<style>
    .page-hero {
        max-width: 48rem;
        margin: 1.5rem auto 1rem;
        padding: 0 1rem;
    }

    .page-hero h1 {
        color: var(--main-color);
        font-size: 2rem;
        font-weight: 700;
        letter-spacing: -0.02em;
        text-align: center;
    }


    .return-button {
        display: inline-block;
        width: 94%;
        color: #ffffff !important;
        background-color: #b44444;
        padding: var(--button-padding);
        border: 3px solid rgba(255, 255, 255, 0.295);
        border-radius: var(--button-border-radius);
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
</head>
<body class="relative">
<?php require_once('header.php'); ?>
<?php
    require_once('database/dbEvents.php');

    $errors = [];
    $args = [];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $args = sanitize($_POST);

        $required = ['plate', 'capacity'];

        if (!wereRequiredFieldsSubmitted($args, $required)) {
            $errors[] = 'Please fill in all required fields.';
        }

        $plate    = trim($args['plate']);
      //  $vin      = trim($args['vin']);
        $capacity = (int) $args['capacity'];
        $wheelchair_accessible = isset($args['wheelchair_accessible']) ? 1 : 0;
        $make_model = trim($args['make_model'] ?? '');
        $notes      = trim($args['notes'] ?? '');

        if (empty($errors) && $capacity <= 0) {
            $errors[] = 'Capacity must be a positive number.';
        }

        if (empty($errors)) {
            $result = add_vehicle($plate, $capacity, $wheelchair_accessible, $make_model, $notes);
            if ($result === false) {
                $errors[] = 'Could not add vehicle. Please try again.';
            } else {
                $success = ('Added Vehicle!');
            }
        }
    }
?>

<section class="page-hero">
    <h1>Add Vehicle</h1>
</section>

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

                <label for="plate"><em>* </em>ID</label>
                <input type="text" id="plate" name="plate" required placeholder="Enter vehicle ID number"
                       value="<?= ($args['plate'] ?? '') ?>">

               

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

             <div class="text-center mt-4">
        <a href="driverVehicleManagement.php" class="return-button">Return to Driver &amp; Vehicle Management</a>
        </div>
        </form>

         
    </div>
</main>

</body>
</html>
