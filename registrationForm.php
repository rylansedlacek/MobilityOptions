<!-- imports -->
<script src="https://nosir.github.io/cleave.js/dist/cleave.min.js"></script>
<script src="https://nosir.github.io/cleave.js/dist/cleave-phone.i18n.js"></script>
<!-- Hero Section with Title -->
<body>

    <div class="navbar">
        <div class="left-section">
            <div class="nav-links">
                <div class="nav-item">
                    <p style="color: #4f7dbe; font-family: Nunito, Quicksand, sans-serif; font-weight: 400;">Quick Access</p>
                    <div class="dropdown">
                        <a href="addEvent.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/plus-solid.svg">
    <span>Schedule a Ride</span>
  </div>
</a>
<a href="calendar.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/list-solid.svg">
    <span>View Scheduled Rides</span>
  </div>
</a>
<a href="editHours.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/clock-regular.svg">
    <span>Change Scheduled Ride Time</span>
  </div>
</a>
<a href="viewPendingApps.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/users-solid.svg">
    <span>Pending Applications</span>
  </div>
</a>
<a href="adminViewingEvents.php" style="text-decoration: none;">
  <div class="in-nav">
    <img src="images/list-solid.svg">
    <span>Edit Ride Details</span>
  </div>
</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="center-section">
            <a href="index.php" class="logo-container">
                <img src="images/healthyGenPageLogo.png" alt="Logo" style="height: 5rem;">
            </a>
        </div>

        <div class="right-section">
            <div class="nav-links">
                <div class="nav-item">
                    <div class="icon">
                        <img src="images/usaicon.png" alt="User Icon" class="icon-img in-nav-img">
                        <div class="dropdown" style="left: -100px;">
                            <a href="changePassword.php" class="dropdown-link"><div>Change Password</div></a>
                            <a href="logout.php" class="dropdown-link"><div>Log Out</div></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<main>
    <div class="main-content-box">
        <?php if (!empty($successMessage)) echo $successMessage; ?>
        <form class="signup-form" method="post">
            <div class="text-center spacing-bottom">
                <h2 class="mb-8">Registration Form</h2>
                <div class="info-box">
                    <p class="sub-text">Please fill out each section of the following form to create a rider profile.</p>
                    <p>An asterisk ( <em>*</em> ) indicates a required field.</p>
                </div>
            </div>

            <fieldset class="section-box mb-4">

                <h3 class="mt-2">Rider Information</h3>
                <p class="mb-2">The following information will help identify the rider within the system.</p>
                <div class="blue-div"></div>

                <label for="first_name"><em>* </em>First Name</label>
                <input type="text" id="first_name" name="first_name" required placeholder="Enter first name">

                <label for="last_name"><em>* </em>Last Name</label>
                <input type="text" id="last_name" name="last_name" required placeholder="Enter last name">

                <!-- eligibility status sterf - r -->
                <label for="eligibility_status">Eligibility status</label>
                <select id="eligibility_status" name="eligibility_status">
                    <option value="pending" selected>Pending</option>
                    <option value="approved">Approved</option>
                    <option value="denied">Denied</option>
                </select>

                <label><em>* </em>Do you have any disabilities?</label>
                <div class="radio-group">
                    <div class="radio-element">
                        <input type="radio" id="disYes" name="has_disability" value="yes" required>
                        <label for="disYes">Yes</label>
                    </div>
                    <div class="radio-element">
                        <input type="radio" id="disNo" name="has_disability" value="no">
                        <label for="disNo">No</label>
                    </div>
                </div>


                <div id="disabilityDetails" style="display:none; margin-top: 10px;">
                    <label for="disabilityType"><em>* </em>Disability type</label>
                    <select id="disabilityType" name="disability_type">
                        <option value="">Select one...</option>
                        <option value="mobility">Mobility</option>
                        <option value="vision">Vision</option>
                        <option value="hearing">Hearing</option>
                        <option value="cognitive">Cognitive</option>
                        <option value="other">Other</option>
                    </select>

                    <label for="mobilityNeeds" style="display:block; margin-top:10px;">
                        Mobility needs / accommodations
                    </label>
                    <input
                        type="text"
                        id="mobilityNeeds"
                        name="mobility_needs"
                        placeholder="e.g., wheelchair accessible vehicle, extra time, assistance…" />
                </div>

                <script>
                    const yes = document.getElementById("disYes");
                    const no = document.getElementById("disNo");
                    const details = document.getElementById("disabilityDetails");
                    const disabilityType = document.getElementById("disabilityType");
                    const mobilityNeeds = document.getElementById("mobilityNeeds");

                    function toggleDetails() {
                        if (yes.checked) {
                            details.style.display = "block";
                            disabilityType.required = true;
                        } else {
                            details.style.display = "none";
                            disabilityType.required = false;
                            //in backend we should have an if statement that checks if has_disability is yes before looking at the disability type and mobility needs
                            disabilityType.value = "";
                            mobilityNeeds.value = "";
                        }
                    }

                    yes.addEventListener("change", toggleDetails);
                    no.addEventListener("change", toggleDetails);
                </script>

                <label for="birthdate"><em>* </em>Date of Birth</label>
                <input type="date" id="birthdate" name="birthdate" required placeholder="Enter rider birthday" max="<?php echo date('Y-m-d'); ?>">

                <label for="street_address"><em>* </em>Street Address</label>
                <input type="text" id="street_address" name="street_address" required placeholder="Enter street address">

                <!--<label for="address"><em>* </em>Address</label>
            <input type="text" id="address" name="address" required placeholder="Enter your home address">-->

                <label for="city"><em>* </em>City</label>
                <input type="text" id="city" name="city" required placeholder="Enter city">

                <label for="state"><em>* </em>State</label>

                <select id="state" name="state" required>
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

                <label for="zipcode"><em>* </em>Zip Code</label>
                <input type="text" id="zipcode" name="zipcode" pattern="^\d{5}(-\d{4})?$" required placeholder="Ex: 12345 or 12345-6789">

                <!--<label for="zip"><em>* </em>Zip Code</label>
            <input type="text" id="zip" name="zip" pattern="[0-9]{5}" title="5-digit zip code" required placeholder="Enter your 5-digit zip code">
-->
                <!-- MILITARY INFO - prob dont need, maybe can utilize later 
            <div class="median-div"></div>
            <label for="affiliation"><em>* </em>Military Affiliation</label>
            <select id="affiliation" name="affiliation" required>
                <option value="" disabled selected></option>
                <option value="Active duty">Active duty</option>
                <option value="Family">Family member (spouse, child, or parent)</option>
                <option value="Reserve">Reservist</option>
                <option value="Veteran">Veteran</option>
                <option value="Civilian">Civilian</option>
            </select>

            <label for="branch"><em>* </em>Branch of Service</label>
            <select id="branch" name="branch" required>
                <option value="" disabled selected></option>
                <option value="Air Force">Air Force</option>
                <option value="Army">Army</option>
                <option value="Coast Guard">Coast Guard</option>
                <option value="Marine Corp">Marine Corp</option>
                <option value="Navy">Navy</option>
                <option value="Space Force">Space Force</option>
            </select>
        /*
-->
            </fieldset>


            <fieldset class="section-box mb-4">
                <h3>Contact Information</h3>
                <p class="mb-2">The following information will help determine the best way to contact the rider.</p>
                <div class="blue-div"></div>

                <label for="email"><em>* </em>E-mail</label>
                <input type="email" id="email" name="email" required placeholder="Enter rider e-mail address">

                <!--<label for="email_consent">E-mail Notifications</label>
            <p>By checking the box below, you consent to recieve emails from the Whiskey Valor Foundation. You may change this at any time.</p>
            <label><input type="checkbox" id="email_prefs" name="email_prefs" value="true"> I consent.</label>

            <div class="median-div"></div>-->

                <label for="phone1">Phone Number</label>
                <input type="tel" id="phone1" name="phone1" pattern="(\D{0,1})\d{3}(\D{0,2})\d{3}(.{0,1})\d{4}" placeholder="Ex. (555) 555-5555">

                <div class="median-div"></div>

                <!--emergency contact info stuff -->
                <!--<fieldset class="section-box mb-4"> -->
                <h3>Emergency Contact Information</h3>
                <p class="mb-2">Please provide information for an emergency contact.</p>
                <div class="blue-div"></div>
                <label for="emergency_first_name"><em>* </em>First Name</label>
                <input type="text" id="emergency_first_name" name="emergency_first_name" required placeholder="Enter their first name">

                <label for="emergency_last_name"><em>* </em>Last Name</label>
                <input type="text" id="emergency_last_name" name="emergency_last_name" required placeholder="Enter their last name">
                <label for="emergency_email"><em>* </em>E-mail</label>
                <input type="email" id="emergency_email" name="emergency_email" required placeholder="Enter their email address">

                <label for="emergency_phone"> <em>* </em> Phone Number</label>
                <input type="tel" id="emergency_phone" name="emergency_phone" pattern="(\D{0,1})\d{3}(\D{0,2})\d{3}(.{0,1})\d{4}" required placeholder="Ex. (555) 555-5555">

                <label for="emergency_relation"><em>* </em>Personal Affiliation</label>
                <select id="emergency_relation" name="emergency_relation" required>
                    <option value="" disabled selected></option>
                    <option value="Spouse">Spouse</option>
                    <option value="Family">Family member</option>
                    <option value="Friend">Friend</option>
                    <option value="Legal Guardian">Legal Guardian</option>
                </select>
            </fieldset>


            <!--<label><em>* </em>Phone Type</label>
            <div class="radio-group">
	      <div class="radio-element">
                <input type="radio" id="phone-type-cellphone" name="phone_type" value="cellphone" required><label for="phone-type-cellphone">Cell</label>
	      </div>
	      <div class="radio-element">
                <input type="radio" id="phone-type-home" name="phone_type" value="home" required><label for="phone-type-home">Home</label>
	      </div>
	      <div class="radio-element">
                <input type="radio" id="phone-type-work" name="phone_type" value="work" required><label for="phone-type-work">Work</label>
	      </div>
            </div>-->

            <!-- </fieldset>
        </fieldset> -->
            <!--<fieldset class="section-box mb-4">
            <h3>Emergency Contact</h3>
            <p class="mb-2">Please provide emergency contact information to contact on the riders' behalf in case of an emergency.</p>
	    <div class="blue-div"></div>

            <label for="emergency_contact_first_name" required><em>* </em>Contact First Name</label>
            <input type="text" id="emergency_contact_first_name" name="emergency_contact_first_name" required placeholder="Enter emergency contact first name">

            <label for="emergency_contact_last_name" required><em>* </em>Contact Last Name</label>
            <input type="text" id="emergency_contact_last_name" name="emergency_contact_last_name" required placeholder="Enter emergency contact last name">

            <label for="emergency_contact_relation"><em>* </em>Contact Relation to Rider</label>
            <input type="text" id="emergency_contact_relation" name="emergency_contact_relation" required placeholder="Ex. Spouse, Mother, Father, Sister, Brother, Friend">

            <label for="emergency_contact_phone"><em>* </em>Contact Phone Number</label>
            <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone" pattern="(\D{0,1})\d{3}(\D{0,2})\d{3}(.{0,1})\d{4}" required placeholder="Enter emergency contact phone number. Ex. (555) 555-5555">

            <label><em>* </em>Contact Phone Type</label>
            <div class="radio-group">
	      <div class="radio-element">
                <input type="radio" id="phone-type-cellphone" name="emergency_contact_phone_type" value="cellphone" required><label for="phone-type-cellphone">Cell</label>
	      </div>
	      <div class="radio-element">
                <input type="radio" id="phone-type-home" name="emergency_contact_phone_type" value="home" required><label for="phone-type-home">Home</label>
	      </div>
	      <div class="radio-element">
                <input type="radio" id="phone-type-work" name="emergency_contact_phone_type" value="work" required><label for="phone-type-work">Work</label>
	      </div>
            </div>
        </fieldset>

        <!-- <fieldset class="section-box mb-4"> 
            <h3 class="mb-2">Other Required Information</h3>
	    <div class="blue-div"></div>

           <label><em>* </em>Are you volunteering for court-ordered community service?</label>
            <div class="radio-group">
	      <div class="radio-element">
                <input type="radio" id="yes" name="is_community_service_volunteer" value="yes" required>
                <label for="yes">Yes</label>
	      </div>

	      <div class="radio-element">
                <input type="radio" id="no" name="is_community_service_volunteer" value="no">
                <label for="no">No</label>
	      </div>
            </div>
         
            <label>Are there any specific skills you have that you believe could be useful for volunteering at the FredSPCA</label>
            <input type="text" id="skills" name="skills" placeholder="">

            <label>Any interests/hobbies?</label>
            <input type="text" id="interests" name="interests" placeholder="">


        </fieldset> -->





            <script>
                // Event listeners for changes in volunteer/participant selection and the complete statuses
                //document.querySelectorAll('input[name="is_community_service_volunteer"]').forEach(radio => {
                //  radio.addEventListener('change', toggleTrainingSection);
                //});




                // Initial check on page load
            </script>
            <script>
                // Initialize Cleave.js for primary phone number
                new Cleave('#phone1', {
                    phone: true,
                    phoneRegionCode: 'US',
                    delimiter: '-',
                    numericOnly: true,
                });
                var cleavePhone = new Cleave('#emergency_contact_phone', {
                    phone: true,
                    phoneRegionCode: 'US',
                    delimiter: '-',
                    numericOnly: true,
                });
            </script>

            <script>
                // Initialize Cleave.js for primary phone number
                new Cleave('#emergency_phone', {
                    phone: true,
                    phoneRegionCode: 'US',
                    delimiter: '-',
                    numericOnly: true,
                });
            </script>


            <!--<fieldset class="section-box mb-4">
            <h3>Login Credentials</h3>
            <p class="mb-2">You will use the following information to log in to the system.</p>
	    <div class="blue-div"></div>

            <label for="username"><em>* </em>Username</label>
            <input type="text" id="username" name="username" required placeholder="Enter a username">

            <label for="password"><em>* </em>Password</label>
            <p>Your password must be at least 8 characters long, contain at least one number, one uppercase letter, and one lowercase letter.</p>
            <input type="password" id="password" name="password" placeholder="Enter a strong password" required>
            <p id="password-error" class="error hidden">Password does not meet requirements.</p>

            <label for="password-reenter"><em>* </em>Re-enter Password</label>
            <input type="password" id="password-reenter" name="password-reenter" placeholder="Re-enter password" required>
            <p id="password-match-error" class="error hidden">Passwords do not match.</p>-->

            <!-- Required by backend -->
            <!--<input type="hidden" name="is_new_volunteer" value="1">
        <input type="hidden" name="total_hours_volunteered" value="0"> -->
            <!--</fieldset>-->

            <!--<fieldset class="section-box mb-4">
            <h3>Consent Notice</h3>
            <p class="mb-2">Please review the following before creating your account.</p>
        <div class="blue-div"></div>
            <label><em>* </em> Privacy Policy</label>
            <p>I confirm that I have read the <a href="https://whiskeyvalor.org/policies/privacy-policy">Privacy Policy</a> and consent to the Whiskey Valor Foundation collecting and storing my information for the purposes outlined therein.</p>
            <div class="radio-group">
                <div class="radio-element">
                    <input type="radio" id="agree" name="privacy_consent" value="yes" required>
                    <label for="agree">I agree.</label>
                </div>
                <div class="radio-element">
                    <input type="radio" id="disagree" name="privacy_consent" value="no">
                    <label for="disagree">I do not agree.</label>
                </div>
            </div>
        </fieldset>-->
            <p class="text-center notice"></p>
            <input type="submit" name="registration-form" value="Submit" style="width: 50%; margin: auto;">

            <div class="text-center mt-6">
        <a href="volunteerManagement.php" class="return-button">Return to Rider Management</a>
    </div>
        </form>
    </div>
</main>