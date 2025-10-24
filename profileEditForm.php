<?php
    require_once('domain/Person.php');
    require_once('database/dbPersons.php');
    require_once('include/output.php');

    $args = sanitize($_GET);
    if ($_SESSION['access_level'] >= 2 && isset($args['id'])) {
        $id = $args['id'];
        $editingSelf = $id == $_SESSION['_id'];
        // Check to see if user is a lower-level manager here
    } else {
        $editingSelf = true;
        $id = $_SESSION['_id'];
    }

    $person = retrieve_person($id);
    if (!$person) {
        echo '<main class="signup-form"><p class="error-toast">That user does not exist.</p></main></body></html>';
        die();
    }

    $times = [
        '12:00 AM', '1:00 AM', '2:00 AM', '3:00 AM', '4:00 AM', '5:00 AM',
        '6:00 AM', '7:00 AM', '8:00 AM', '9:00 AM', '10:00 AM', '11:00 AM',
        '12:00 PM', '1:00 PM', '2:00 PM', '3:00 PM', '4:00 PM', '5:00 PM',
        '6:00 PM', '7:00 PM', '8:00 PM', '9:00 PM', '10:00 PM', '11:00 PM',
        '11:59 PM'
    ];
    $values = [
        "00:00", "01:00", "02:00", "03:00", "04:00", "05:00", 
        "06:00", "07:00", "08:00", "09:00", "10:00", "11:00", 
        "12:00", "13:00", "14:00", "15:00", "16:00", "17:00", 
        "18:00", "19:00", "20:00", "21:00", "22:00", "23:00",
        "23:59"
    ];
    
    function buildSelect($name, $disabled=false, $selected=null) {
        global $times;
        global $values;
        if ($disabled) {
            $select = '
                <select id="' . $name . '" name="' . $name . '" disabled>';
        } else {
            $select = '
                <select id="' . $name . '" name="' . $name . '">';
        }
        if (!$selected) {
            $select .= '<option disabled selected value>Select a time</option>';
        }
        $n = count($times);
        for ($i = 0; $i < $n; $i++) {
            $value = $values[$i];
            if ($selected == $value) {
                $select .= '
                    <option value="' . $values[$i] . '" selected>' . $times[$i] . '</option>';
            } else {
                $select .= '
                    <option value="' . $values[$i] . '">' . $times[$i] . '</option>';
            }
        }
        $select .= '</select>';
        return $select;
    }
?>

<!DOCTYPE html>
<html lang="en">
    <body>
        <main>
                <?php if (isset($updateSuccess)): ?>
                        <?php if ($updateSuccess): ?>
                            <div class="happy-toast">Profile updated successfully!</div>
                        <?php else: ?>
                            <div class="error-toast">An error occurred.</div>
                        <?php endif ?>
                <?php endif ?>
                <?php if ($isAdmin): ?>
                    <?php if (strtolower($id) == 'vmsroot') : ?>
                        <div class="error-toast">The root user profile cannot be modified</div></main></body>
                    <?php die() ?>
                    <?php elseif (isset($_GET['id']) && $_GET['id'] != $_SESSION['_id']): ?>
                    <!-- <a class="button" href="modifyUserRole.php?id=<?php echo htmlspecialchars($_GET['id']) ?>">Modify User Access</a> -->
                    <?php endif ?>
                <?php endif ?>

            
                <div class="text-center mb-8">
                    <h2>Edit User Account</h2>
                    <p class="sub-text">Edit a staff member's account details.</p>
                    <br>
                    <p>An asterisk (<em>*</em>) indicates a required field.</p>
                    <br>
                </div>

                <form id="edit-user-form" class="w-full" method="post">
                    <fieldset class="text-center mb-8">
                        <div>
                            <h3>Login Credentials</h3>
                            <label>Username</label>
                            <legend><?php echo $person->get_id() ?></legend>
                        </div>

                        <!--<label>Password</label>-->
                        <div>
                            <p style="color: red"><a href='changePassword.php'>Change Password</a></p>
                        </div>
                            
                        <div>
                            <label>User Role</label>
                            <select id="user_role" name="user_role" class="w-full" required>
                            <option value="admin" <?php if (isset($user_role) && $user_role == 'admin') echo 'selected'; ?>>Admin - Full Access</option>
                            <option value="case_manager" <?php if (isset($user_role) && $user_role == 'case_manager') echo 'selected'; ?>>Case Manager - Lease + Maintenance</option>
                            <option value="maintenance" <?php if (isset($user_role) && $user_role == 'maintenance') echo 'selected'; ?>>Maintenance Staff - Maintenance Only</option>
                        </div>
                </select>
                        </div>
                        <br>
                            
                    </fieldset>

                    <fieldset class="section-box">
                        <h3 class="text-center mb-8">Personal Information</h3>
                        <label for="first_name"><em>* </em>First Name</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo hsc($person->get_first_name()); ?>" required placeholder="Enter your first name">

                        <label for="last_name"><em>* </em>Last Name</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo hsc($person->get_last_name()); ?>" required placeholder="Enter your last name">

                        <label for="birthday"><em>* </em>Date of Birth</label>
                        <input type="date" id="birthday" name="birthday" value="<?php echo hsc($person->get_birthday()); ?>" required placeholder="Choose your birthday" max="<?php echo date('Y-m-d'); ?>">


                        <label for="street_address"><em>* </em>Street Address</label>
                        <input type="text" id="street_address" name="street_address" value="<?php echo hsc($person->get_street_address()); ?>" required placeholder="Enter your street address">

                        <label for="city"><em>* </em>City</label>
                        <input type="text" id="city" name="city" value="<?php echo hsc($person->get_city()); ?>" required placeholder="Enter your city">

                        <label for="state"><em>* </em>State</label>
                        <select id="state" name="state" required>
                            <?php
                                $state = $person->get_state();
                                $states = array(
                                    'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado', 'Connecticut', 'Delaware', 'District Of Columbia', 'Florida', 'Georgia', 'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa', 'Kansas', 'Kentucky', 'Louisiana', 'Maine', 'Maryland', 'Massachusetts', 'Michigan', 'Minnesota', 'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada', 'New Hampshire', 'New Jersey', 'New Mexico', 'New York', 'North Carolina', 'North Dakota', 'Ohio', 'Oklahoma', 'Oregon', 'Pennsylvania', 'Rhode Island', 'South Carolina', 'South Dakota', 'Tennessee', 'Texas', 'Utah', 'Vermont', 'Virginia', 'Washington', 'West Virginia', 'Wisconsin', 'Wyoming'
                                );
                                $abbrevs = array(
                                    'AL', 'AK', 'AZ', 'AR', 'CA', 'CO', 'CT', 'DE', 'DC', 'FL', 'GA', 'HI', 'ID', 'IL', 'IN', 'IA', 'KS', 'KY', 'LA', 'ME', 'MD', 'MA', 'MI', 'MN', 'MS', 'MO', 'MT', 'NE', 'NV', 'NH', 'NJ', 'NM', 'NY', 'NC', 'ND', 'OH', 'OK', 'OR', 'PA', 'RI', 'SC', 'SD', 'TN', 'TX', 'UT', 'VT', 'VA', 'WA', 'WV', 'WI', 'WY'
                                );
                                $length = count($states);
                                for ($i = 0; $i < $length; $i++) {
                                    if ($abbrevs[$i] == $state) {
                                        echo '<option value="' . $abbrevs[$i] . '" selected>' . $states[$i] . '</option>';
                                    } else {
                                        echo '<option value="' . $abbrevs[$i] . '">' . $states[$i] . '</option>';
                                    }
                                }
                            ?>
                        </select>

                        <label for="zip_code"><em>* </em>Zip Code</label>
                        <input type="text" id="zip_code" name="zip_code" value="<?php echo hsc($person->get_zip_code()); ?>" pattern="[0-9]{5}" title="5-digit zip code" required placeholder="Enter your 5-digit zip code">
                    </fieldset>

                    <fieldset class="section-box">
                        <br>
                        <h3 class="text-center mb-8">Contact Information</h3>

                        <label for="email"><em>* </em>E-mail</label>
                        <input type="email" class="w-full" id="email" name="email" value="<?php echo hsc($person->get_email()); ?>" required placeholder="Enter your e-mail address">

                        <label for="phone1"><em>* </em>Phone Number</label>
                        <input type="tel" id="phone1" name="phone1" value="<?php echo formatPhoneNumber($person->get_phone1()); ?>" pattern="\([0-9]{3}\) [0-9]{3}-[0-9]{4}" required placeholder="Ex. (555) 555-5555">

                        <label><em>* </em>Phone Type</label>
                        <div class="radio-group">
                            <?php $type = $person->get_phone1type(); ?>
                            <input type="radio" id="phone-type-cellphone" name="phone1type" value="cellphone" <?php if ($type == 'cellphone') echo 'checked'; ?> required><label for="phone-type-cellphone">Cell</label>
                            <input type="radio" id="phone-type-home" name="phone1type" value="home" <?php if ($type == 'home') echo 'checked'; ?> required><label for="phone-type-home">Home</label>
                            <input type="radio" id="phone-type-work" name="phone1type" value="work" <?php if ($type == 'work') echo 'checked'; ?> required><label for="phone-type-work">Work</label>
                        </div>
                    </fieldset>
                    <br>
                    <div class="text-center">
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                        <input type="submit" name="profile-edit-form" value="Update Profile" class="blue-button">
                        <br>
                        <br>
                        <?php if ($editingSelf): ?>
                            <a class="button cancel" href="viewProfile.php" style="margin-top: -.5rem">Cancel</a>
                        <?php else: ?>
                            <a class="button cancel" href="viewProfile.php?id=<?php echo htmlspecialchars($_GET['id']) ?>" style="margin-top: -.5rem">Cancel</a>
                        <?php endif ?>
                    </div>
                    
                </form>
        </main>
    </body>
</html>
