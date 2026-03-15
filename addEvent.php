<?php session_cache_expire(30);
    session_start();
    // Make session information accessible, allowing us to associate
    // data with the logged-in user.

    ini_set("display_errors",1);
    error_reporting(E_ALL);

    $loggedIn = false;
    $accessLevel = 0;
    $userID = null;
    if (isset($_SESSION['_id'])) {
        $loggedIn = true;
        // 0 = not logged in, 1 = standard user, 2 = manager (Admin), 3 super admin (TBI)
        $accessLevel = $_SESSION['access_level'];
        $userID = $_SESSION['_id'];
    } 
    // Require admin privileges
    if ($accessLevel < 2) {
        header('Location: login.php');
        //echo 'bad access level';
        die();
    }
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        require_once('include/input-validation.php');
        require_once('database/dbEvents.php');
        $args = sanitize($_POST, null);
        $args['type'] = 'Normal'; // default to "Normal" type for now since we removed the form option 
        $required = array(
            //type needed? I deleted it, it was on the end behind "description". The form part for it is commented out as well - GC
            "name", "date", "start-time", "end-time", "description", "type"
        );
        if (!wereRequiredFieldsSubmitted($args, $required)) { 
            echo 'bad form data';
            die();
        } else {
           
            $args['driver_id'] = null; // set driver_id to null - sprint 3
            $args['vehicle_id'] = null; // set vehicle_id to null - sprint 3
            
            // rider name population - rs
            if (!empty($args['rider_id'])) {
                // search with rider_id
                require_once('database/dbPersons.php');
                $rider = retrieve_person($args['rider_id']);

                if (!$rider) {
                    ?>
                    <script>
                        alert('Rider Profile does not exist. Please search again.');
                        history.back();
                    </script>
                    <?php
                    die();
                }

            } elseif (!empty($args['name'])) {
                // search with name value
                require_once('database/dbPersons.php');
                $riders = retrieve_persons_by_name($args['name']);
                
                if (empty($riders)) {
                    ?>
                    <script>
                         alert('Rider Profile not found. Please try again.');
                        history.back();
                    </script>
                    <?php
                    die();
                }
                $args['rider_id'] = $riders[0]->get_id(); // set rider_id for use in dbevents.

            } else {
                ?>
                    <script>
                         alert('No rider selected. Please search and select a rider.');
                        history.back();
                    </script>
                <?php
            }
            
            if (validate24hTimeRange($args['start-time'], $args['end-time'])) {
                $startTime = $args['start-time'];
                $endTime = $args['end-time'];
            } else {
                $validated = validate12hTimeRangeAndConvertTo24h($args["start-time"], $args["end-time"]);
                if (!$validated) {
                    echo 'bad time range';
                    die();
                }
                $startTime = $args['start-time'] = $validated[0];
                $endTime = $args['end-time'] = $validated[1];
            }
            $date = $args['date'] = validateDate($args["date"]);
            $args["training_level_required"] = $_POST['training_level_required'] ?? 'None';
    
            $args['startDate'] = $date;
            $args['endDate']   = $date;   
            $args['startTime'] = $startTime;
            $args['endTime']   = $endTime;


            //1. Start of use case #8 recurring, etc
            $isRecurring = isset($_POST['recurring']) ? 1 : 0;
            $recurrenceType = $isRecurring ? ($_POST['recurrence_type'] ?? '') : '';
            $customDays = ($isRecurring && $recurrenceType === 'custom') ? (int)($_POST['custom_days'] ?? 0) : null;

            
            if ($isRecurring) {
                if (!in_array($recurrenceType, ['daily','weekly','monthly','custom'], true)) {
                    echo 'invalid recurrence type';
                    die();
                }
                if ($recurrenceType === 'custom' && (!$customDays || $customDays < 1)) {
                    echo 'invalid custom interval';
                    die();
                }
                $args['is_recurring'] = 1;
                $args['recurrence_type'] = $recurrenceType;                  // daily|weekly|monthly|custom
                $args['recurrence_interval_days'] = ($recurrenceType === 'custom') ? $customDays : null;
            } else {
                $args['is_recurring'] = 0;
                $args['recurrence_type'] = null;
                $args['recurrence_interval_days'] = null;
            }
            //1. Start of use case #8 recurring, etc

            // FIXED: Replaced the broken check "if (!$date > 11)"
            if (!$startTime || !$endTime || !$date){
                echo 'bad args';
                die();
            }

            // combine address fields into single pickup and dropoff locations
            $args['pickup_location'] = $args['pickup-street_address'] . ', ' . 
                                       $args['pickup-city'] . ', ' . 
                                       $args['pickup-state'] . ' ' . 
                                       $args['pickup-zipcode'];
            
            $args['dropoff_location'] = $args['dropoff-street_address'] . ', ' . 
                                        $args['dropoff-city'] . ', ' . 
                                        $args['dropoff-state'] . ' ' . 
                                        $args['dropoff-zipcode'];

            $args['series_id'] = bin2hex(random_bytes(16)); // new new
            $args['completed'] = 'N';

            $id = create_event($args);
            if (!$id) {
                die();
            } else {
    
                $counts = [
                    'daily'   => 30,  // next 30 days
                    'weekly'  => 12,  // next 12 weeks
                    'monthly' => 6,   // next 6 months
                    'custom'  => 12,  // 12 custom intervals
                ];
                
                $intervalMap = [
                    'daily'   => 'P1D',
                    'weekly'  => 'P1W',
                    'monthly' => 'P1M',
                ];
                if ($recurrenceType === 'custom') {
                    $customDays = max(1, $customDays);
                    $intervalSpec = 'P' . $customDays . 'D';
                } else {
                    $intervalSpec = $intervalMap[$recurrenceType] ?? null;
                }

                if ($isRecurring && $intervalSpec && isset($counts[$recurrenceType])) {
                    $current = new DateTime($args['startDate']);  
                    $step    = new DateInterval($intervalSpec);
                    $times   = $counts[$recurrenceType];

                    for ($i = 0; $i < $times; $i++) {
                        $current->add($step);
                        $ymd = $current->format('Y-m-d');

                        $dup = $args;
             
                        $dup['completed'] = 'N';
                        $dup['startDate'] = $ymd;
                        $dup['endDate']   = $ymd;
                        $dup['date']      = $ymd;    

                        create_event($dup);
                    }
                }
                
                header('Location: eventSuccess.php');
                exit();
            }
        }
    }
    
    $date = null;
    if (isset($_GET['date'])) {
        $date = $_GET['date'];
        $datePattern = '/[0-9]{4}-[0-9]{2}-[0-9]{2}/';
        $timeStamp = strtotime($date);
        if (!preg_match($datePattern, $date) || !$timeStamp) {
            header('Location: calendar.php');
            die();
        }
    }

    include_once('database/dbinfo.php'); 
    $con=connect();  

    
    /*
        Searching Logic - rs
        - search results are stored for display
        - find_users is a dbpersons function which returns values for display.
        - the block is used below right at the top of the HTMl
    */
    $search_results = [];    

    if (isset($_GET['search_name'])) {
        require_once('include/input-validation.php');
        require_once('database/dbPersons.php');
        $namePass = trim($_GET['search_name']);
        $search_results = find_users($namePass, '', '', '', null, null); // dbpersons name search
    }
    
?><!DOCTYPE html>
<header class="hero-header">
    <div class="center-header">
        <h1>Ride Scheduling</h1>
    </div>
</header>
<html>
    <head>
        <?php require_once('universal.inc') ?>
        <title>Healthy Generations | Ride Request</title>
    </head>
    <body>
        <?php require_once('header.php') ?>
        
        <main class="date">
            
            <form id="new-event-form" method="POST">
                

                <div class="event-sect">
                    <h2 class="mt-2">Rider Search</h2>
                    <div id="rider-lookup" style="margin-bottom:12px;">
                        <input type="text" id="search_name" placeholder="Type name and click Search" value="<?php echo isset($_GET['search_name']) ? htmlspecialchars($_GET['search_name']) : ''; ?>" style="width:100%; padding:6px;">
                        <button type="button" id="search_button" style="background:#45892e;color:#fff;border:none;cursor:pointer;">Search</button>
                    </div>
                    <script>
                        // passes the entered name value to the searching logic above. - rs
                        document.getElementById('search_button').addEventListener('click', function() {
                            const searchValue = document.getElementById('search_name').value.trim();
                            if(searchValue.length > 0){
                                window.location = 'addEvent.php?search_name=' + encodeURIComponent(searchValue);
                            }
                        });
                    </script>
                    <?php if (!empty($search_results)): ?>
                        <h3 class="mt-2">Search Results</h3>
                        <ul style="list-style:none; padding:0; margin-bottom:12px; max-height:150px; overflow:auto; border:2px solid #45892e; border-radius:4px;">
                            <?php foreach ($search_results as $rider): ?>
                                <li style="padding:6px; border-bottom:1px solid #eee; cursor:pointer;" onclick="selectRider('<?php echo $rider->get_first_name().' '.$rider->get_last_name(); ?>','<?php echo $rider->get_id(); ?>')">
                                    <?php echo $rider->get_first_name().' '.$rider->get_last_name(); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

                 <div class="event-sect">
                    <h2 class="mt-2">Rider Information</h2>
                    <label for="name">* Rider Name </label>
                    <input type="text" id="name" name="name" required placeholder="Enter name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                    <input type="hidden" id="rider_id" name="rider_id" value="<?php echo isset($_POST['rider_id']) ? htmlspecialchars($_POST['rider_id']) : ''; ?>">
                 </div>

                <div class="event-sect">
                <h2 class="mt-2">Pickup Information</h2>
                <div class="event-datetime">
                <div class="event-date">
                    <label for="date">* Pickup Date </label>
                    <input type="date" id="date" name="date" <?php if ($date) echo 'value="' . $date . '"'; ?> min="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div class="event-date">
                    <label for="start-time">* Start Time </label>
                    <input type="time" id="start-time" name="start-time" required>
                </div>

                
            </div>
                

                <label for="pickup-street_address"><em>* </em>Street Address</label>
                <input type="text" id="pickup-street_address" name="pickup-street_address" required placeholder="Enter street address">

                <label for="pickup-city"><em>* </em>City</label>
                <input type="text" id="pickup-city" name="pickup-city" required placeholder="Enter city">

                <label for="pickup-state"><em>* </em>State</label>

                <select id="pickup-state" name="pickup-state" required>
                    <option value="AL">Alabama</option>
                    <option value="AK">Alaska</option>
                    <option value="AZ">Arizona</option>
                    <option value="AR">Arkansas</option>
                    <option value="CA">California</option>
                    <option value="CO">Colorado</option>
                    <option value="CT">Connecticut</option>
                    <option value="DE">Delaware</option>
                    <option value="DC">District Of Columbia</option>
                    <option value="FL">Florida</option>
                    <option value="GA">Georgia</option>
                    <option value="HI">Hawaii</option>
                    <option value="ID">Idaho</option>
                    <option value="IL">Illinois</option>
                    <option value="IN">Indiana</option>
                    <option value="IA">Iowa</option>
                    <option value="KS">Kansas</option>
                    <option value="KY">Kentucky</option>
                    <option value="LA">Louisiana</option>
                    <option value="ME">Maine</option>
                    <option value="MD">Maryland</option>
                    <option value="MA">Massachusetts</option>
                    <option value="MI">Michigan</option>
                    <option value="MN">Minnesota</option>
                    <option value="MS">Mississippi</option>
                    <option value="MO">Missouri</option>
                    <option value="MT">Montana</option>
                    <option value="NE">Nebraska</option>
                    <option value="NV">Nevada</option>
                    <option value="NH">New Hampshire</option>
                    <option value="NJ">New Jersey</option>
                    <option value="NM">New Mexico</option>
                    <option value="NY">New York</option>
                    <option value="NC">North Carolina</option>
                    <option value="ND">North Dakota</option>
                    <option value="OH">Ohio</option>
                    <option value="OK">Oklahoma</option>
                    <option value="OR">Oregon</option>
                    <option value="PA">Pennsylvania</option>
                    <option value="RI">Rhode Island</option>
                    <option value="SC">South Carolina</option>
                    <option value="SD">South Dakota</option>
                    <option value="TN">Tennessee</option>
                    <option value="TX">Texas</option>
                    <option value="UT">Utah</option>
                    <option value="VT">Vermont</option>
                    <option value="VA" selected>Virginia</option>
                    <option value="WA">Washington</option>
                    <option value="WV">West Virginia</option>
                    <option value="WI">Wisconsin</option>
                    <option value="WY">Wyoming</option>
                </select>

                <label for="pickup-zipcode"><em>* </em>Zip Code</label>
                <input type="text" id="pickup-zipcode" name="pickup-zipcode" pattern="^\d{5}(-\d{4})?$" required placeholder="Ex: 12345 or 12345-6789">

                </div> 

                <div class="event-sect">
                <h2 class="mt-2">Drop-Off Information</h2>
                <div class="event-datetime">
               
                <div class="event-date">
                    <label for="end-time">* End Time </label>
                    <input type="time" id="end-time" name="end-time" required>
                </div>
            </div>
                

                <label for="dropoff-street_address"><em>* </em>Street Address</label>
                <input type="text" id="dropoff-street_address" name="dropoff-street_address" required placeholder="Enter street address">

                <label for="dropoff-city"><em>* </em>City</label>
                <input type="text" id="dropoff-city" name="dropoff-city" required placeholder="Enter city">

                <label for="dropoff-state"><em>* </em>State</label>

                <select id="dropoff-state" name="dropoff-state" required>
                    <option value="AL">Alabama</option>
                    <option value="AK">Alaska</option>
                    <option value="AZ">Arizona</option>
                    <option value="AR">Arkansas</option>
                    <option value="CA">California</option>
                    <option value="CO">Colorado</option>
                    <option value="CT">Connecticut</option>
                    <option value="DE">Delaware</option>
                    <option value="DC">District Of Columbia</option>
                    <option value="FL">Florida</option>
                    <option value="GA">Georgia</option>
                    <option value="HI">Hawaii</option>
                    <option value="ID">Idaho</option>
                    <option value="IL">Illinois</option>
                    <option value="IN">Indiana</option>
                    <option value="IA">Iowa</option>
                    <option value="KS">Kansas</option>
                    <option value="KY">Kentucky</option>
                    <option value="LA">Louisiana</option>
                    <option value="ME">Maine</option>
                    <option value="MD">Maryland</option>
                    <option value="MA">Massachusetts</option>
                    <option value="MI">Michigan</option>
                    <option value="MN">Minnesota</option>
                    <option value="MS">Mississippi</option>
                    <option value="MO">Missouri</option>
                    <option value="MT">Montana</option>
                    <option value="NE">Nebraska</option>
                    <option value="NV">Nevada</option>
                    <option value="NH">New Hampshire</option>
                    <option value="NJ">New Jersey</option>
                    <option value="NM">New Mexico</option>
                    <option value="NY">New York</option>
                    <option value="NC">North Carolina</option>
                    <option value="ND">North Dakota</option>
                    <option value="OH">Ohio</option>
                    <option value="OK">Oklahoma</option>
                    <option value="OR">Oregon</option>
                    <option value="PA">Pennsylvania</option>
                    <option value="RI">Rhode Island</option>
                    <option value="SC">South Carolina</option>
                    <option value="SD">South Dakota</option>
                    <option value="TN">Tennessee</option>
                    <option value="TX">Texas</option>
                    <option value="UT">Utah</option>
                    <option value="VT">Vermont</option>
                    <option value="VA" selected>Virginia</option>
                    <option value="WA">Washington</option>
                    <option value="WV">West Virginia</option>
                    <option value="WI">Wisconsin</option>
                    <option value="WY">Wyoming</option>
                </select>

                <label for="dropoff-zipcode"><em>* </em>Zip Code</label>
                <input type="text" id="dropoff-zipcode" name="dropoff-zipcode" pattern="^\d{5}(-\d{4})?$" required placeholder="Ex: 12345 or 12345-6789">



                </div> 
                
                 <div class="event-sect">
                <label for="name">* Description </label>
                <input type="text" id="description" name="description" required placeholder="Enter description (e.g. 'Ride to VA hospital')">

                <!-- is this somthing we want to keep? idk what "type" would be
                <label for="name">* Ride Type </label>
                <select id="type" name="type">
                    <option value="Normal">Normal</option>
                    <option value="Retreat">Retreat</option>
                </select>
                </div>
-->
                <!--
                <div class="event-sect">
                <label for="name">* Event Visibility</label>
                <p class="sub-text" style="margin-bottom: 1rem;">Visibility controls who can see the event listing on the calendar.</p>
                <div class="radio-group">
                    <div class="radio-element">
                    <label>
                        <input type="radio" name="visibility" value="public" checked>Public
                    </label>
                    </div>
                    <div class="radio-element">
                    <label>
                        <input type="radio" name="visibility" value="private">Private
                    </label>
                    </div>
                </div>
                </div>

                <div class="event-sect">
                <label for="name">* Sign-up Restrictions</label>
                <p class="sub-text">Restrictions control who can sign up for your event.</p>
                <div class="dropdown-group">
                    <div class="dd">
                    <label for="branch">Branch</label>
                    <select  name="branch" id="branch">
                        <option value="all">(any)</option>
                        <option value="air force">Air Force</option>
                        <option value="army">Army</option>
                        <option value="coast guard">Coast Guard</option>
                        <option value="marine">Marine Corp</option>
                        <option value="navy">Navy</option>
                        <option value="space force">Space Force</option>
                    </select>
                    </div>
                    <div class="dd">
                    <label for="affiliation">Affiliation</label>
                    <select  name="affiliation" id="affiliation">
                        <option value="all">(any)</option>
                        <option value="active duty">Active duty</option>
                        <option value="family">Family member (spouse, child, or parent)</option>
                        <option value="reserve">Reservist</option>
                        <option value="veteran">Veteran</option>
                        <option value="civilian">Civilian</option>
                    </select>
                    </div>
                </div>
                </div>
-->
                <!--
                <div class="event-sect">
                <label for="name">Location </label>
                <input type="text" id="location" name="location" placeholder="Enter location">

                <label for="name">* Capacity </label>
                <input type="number" id="capacity" name="capacity" required placeholder="Enter capacity (e.g. 1-99)">
                </div> -->

                <fieldset style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                    <legend>Make this a recurring event</legend>

                    <label style="margin-top:12px; padding:12px; border:1px solid #e0e0e0; border-radius:8px;">
                        <input type="checkbox" id="recurring" name="recurring" value="1">
                        Recurring
                    </label>

                    <div id="recurring-options" style="display:none; margin-top:6px;">
                        <label for="recurrence_type">Recurrence:</label>
                        <select name="recurrence_type" id="recurrence_type">
                            <option value="">-- Select --</option>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="custom">Custom</option>
                        </select>

                        <div id="custom-interval" style="display:none; margin-top:8px;">
                            <label for="custom_days">Repeat every:</label>
                            <input type="number" min="1" id="custom_days" name="custom_days" placeholder="e.g. 10">
                            <span>days</span>
                        </div>
                    </div>
                </fieldset>
                
                <input type="submit" value="Create Event" style="width:100%;">
                
            </form>
                <script>
                    // Debug: log submit attempts and list invalid fields
                    (function(){
                        const form = document.getElementById('new-event-form');
                        if(!form) return;
                        form.addEventListener('submit', function(e){
                            try{
                                console.log('addEvent form submit event', e);
                                const ok = form.checkValidity();
                                console.log('form.checkValidity()', ok);
                                if(!ok){
                                    e.preventDefault();
                                    const invalids = [];
                                    form.querySelectorAll(':invalid').forEach(function(el){ invalids.push({name: el.name, type: el.type, value: el.value}); });
                                    console.error('Form invalid fields:', invalids);
                                    alert('Form validation failed for: ' + invalids.map(i=>i.name).join(', '));
                                } else {
                                    console.log('Form appears valid; letting submit proceed');
                                }
                            }catch(err){
                                console.error('Error in submit debug handler', err);
                            }
                        }, false);
                    })();
                </script>
                <!--
                <?php if ($date): ?>
                    <a class="button cancel" href="calendar.php?month=<?php echo substr($date, 0, 7) ?>" style="margin-top: -.5rem">Return to Calendar</a>
                <?php else: ?>
                    <a class="button cancel" href="index.php" style="margin-top: -.5rem">Return to Dashboard</a>
                <?php endif ?> -->

                <script type="text/javascript">
                    // populate the rider name and id when a search result is clicked
                    function selectRider(name, id) {
                        document.getElementById('name').value = name;
                        document.getElementById('rider_id').value = id;
                        history.replaceState(null, '', 'addEvent.php');
                        
                    }

                    $(document).ready(function(){
                        var checkboxes = $('.checkboxes');
                        checkboxes.change(function(){
                            if($('.checkboxes:checked').length>0) {
                                checkboxes.removeAttr('required');
                            } else {
                                checkboxes.attr('required', 'required');
                            }
                        });
                    });

                    (function(){
                        const recurring = document.getElementById('recurring');
                        const options = document.getElementById('recurring-options');
                        const recurrenceType = document.getElementById('recurrence_type');
                        const customBlock = document.getElementById('custom-interval');
                        const customDays = document.getElementById('custom_days');

                        function toggleOptions(){
                            const on = recurring && recurring.checked;
                            if (options) options.style.display = on ? 'block' : 'none';
                            if (!on) {
                                if (recurrenceType) recurrenceType.value = '';
                                if (customBlock) customBlock.style.display = 'none';
                                if (customDays) customDays.value = '';
                            }
                        }
                        function toggleCustom(){
                            if (!recurrenceType || !customBlock) return;
                            customBlock.style.display = (recurrenceType.value === 'custom') ? 'block' : 'none';
                            customBlock.style.display = (recurrenceType.value === 'custom') ? 'block' : 'none';
                        }

                        if (recurring) {
                            recurring.addEventListener('change', toggleOptions);
                            toggleOptions();
                        }
                        if (recurrenceType) {
                            recurrenceType.addEventListener('change', toggleCustom);
                            toggleCustom();
                        }
                    })();
                </script>
                <br/>
                <br/>
                <center><a class="button cancel" href="index.php">Return to Dashboard</a></center>
                 
        </main>
        
    </body>
</html>