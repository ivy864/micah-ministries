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
    <?php if (isset($errorDetails) && count($errorDetails) > 0): ?>
        <div class="alert alert-error">
            Please correct the following errors:
            <ul style="margin: 10px 0 0 20px;">
                <?php foreach ($errorDetails as $field => $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <main>
        <div class="form-container">
            <form id="edit-user-form" method="post">
                
                <!-- Login Credentials Section -->
                <div class="section-box">
                    <h3>Login Credentials</h3>
                    
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" value="<?php echo htmlspecialchars($person->get_id()) ?>" readonly style="background-color: #f5f5f5;">
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <p style="margin: 5px 0;"><a href="managePassword.php<?php if ($id != $_SESSION['_id']) echo '?user_id=' . $id ?>" style="color: #274471;">Change Password</a></p>
                    </div>
                        
                    <div class="form-group">
                        <label for="user_role">User Role</label>
                        <select id="user_role" name="user_role" required>
                            <?php 
                            $currentRole = $person->get_type();
                            ?>
                            <option value="admin" <?php echo ($currentRole == 'admin') ? 'selected' : ''; ?>>Admin - Full Access</option>
                            <option value="case_manager" <?php echo ($currentRole == 'case_manager') ? 'selected' : ''; ?>>Case Manager - Lease + Maintenance</option>
                            <option value="maintenance" <?php echo ($currentRole == 'maintenance') ? 'selected' : ''; ?>>Maintenance Staff - Maintenance Only</option>
                        </select>
                    </div>
                </div>

                <!-- Personal Information Section -->
                <div class="section-box">
                    <h3>Personal Information</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name *</label>
                            <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($person->get_first_name()); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name *</label>
                            <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($person->get_last_name()); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="birthday">Date of Birth *</label>
                        <input type="date" id="birthday" name="birthday" value="<?php echo htmlspecialchars($person->get_birthday()); ?>" required max="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="street_address">Street Address *</label>
                        <input type="text" id="street_address" name="street_address" value="<?php echo htmlspecialchars($person->get_street_address()); ?>" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="city">City *</label>
                            <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($person->get_city()); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="state">State *</label>
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
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="zip_code">Zip Code *</label>
                        <input type="text" id="zip_code" name="zip_code" value="<?php echo htmlspecialchars($person->get_zip_code()); ?>" pattern="[0-9]{5}" title="5-digit zip code" required>
                    </div>
                </div>

                <!-- Contact Information Section -->
                <div class="section-box">
                    <h3>Contact Information</h3>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($person->get_email()); ?>" required>
                        <?php if (isset($errorDetails['email'])): ?>
                            <p style="color: #721c24; margin-top: 5px; font-size: 14px;"><?php echo htmlspecialchars($errorDetails['email']); ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="phone1">Phone Number *</label>
                        <input type="tel" id="phone1" name="phone1" value="<?php echo formatPhoneNumber($person->get_phone1()); ?>" pattern="\([0-9]{3}\) [0-9]{3}-[0-9]{4}" required>
                    </div>

                    <div class="form-group">
                        <label>Phone Type *</label>
                        <div class="radio-group">
                            <?php $type = $person->get_phone1type(); ?>
                            <input type="radio" id="phone-type-cellphone" name="phone1type" value="cellphone" <?php if ($type == 'cellphone') echo 'checked'; ?> required>
                            <label for="phone-type-cellphone">Cell</label>
                            <input type="radio" id="phone-type-home" name="phone1type" value="home" <?php if ($type == 'home') echo 'checked'; ?> required>
                            <label for="phone-type-home">Home</label>
                            <input type="radio" id="phone-type-work" name="phone1type" value="work" <?php if ($type == 'work') echo 'checked'; ?> required>
                            <label for="phone-type-work">Work</label>
                        </div>
                    </div>
                </div>

                <!-- Emergency Contact Section -->
                <div class="section-box">
                    <h3>Emergency Contact Information</h3>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="emergency_contact_first_name">Emergency Contact First Name *</label>
                            <input type="text" id="emergency_contact_first_name" name="emergency_contact_first_name" value="<?php echo htmlspecialchars($person->get_emergency_contact_first_name()); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="emergency_contact_last_name">Emergency Contact Last Name *</label>
                            <input type="text" id="emergency_contact_last_name" name="emergency_contact_last_name" value="<?php echo htmlspecialchars($person->get_emergency_contact_last_name()); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="emergency_contact_phone">Emergency Contact Phone *</label>
                        <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone" value="<?php echo formatPhoneNumber($person->get_emergency_contact_phone()); ?>" pattern="\([0-9]{3}\) [0-9]{3}-[0-9]{4}" required>
                    </div>

                    <div class="form-group">
                        <label>Emergency Contact Phone Type *</label>
                        <div class="radio-group">
                            <?php $emergencyType = $person->get_emergency_contact_phone_type(); ?>
                            <input type="radio" id="emergency-phone-type-cellphone" name="emergency_contact_phone_type" value="cellphone" <?php if ($emergencyType == 'cellphone') echo 'checked'; ?> required>
                            <label for="emergency-phone-type-cellphone">Cell</label>
                            <input type="radio" id="emergency-phone-type-home" name="emergency_contact_phone_type" value="home" <?php if ($emergencyType == 'home') echo 'checked'; ?> required>
                            <label for="emergency-phone-type-home">Home</label>
                            <input type="radio" id="emergency-phone-type-work" name="emergency_contact_phone_type" value="work" <?php if ($emergencyType == 'work') echo 'checked'; ?> required>
                            <label for="emergency-phone-type-work">Work</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="emergency_contact_relation">Emergency Contact Relation *</label>
                        <input type="text" id="emergency_contact_relation" name="emergency_contact_relation" value="<?php echo htmlspecialchars($person->get_emergency_contact_relation()); ?>" required>
                    </div>
                </div>

                <!-- Skills and Interests Section -->

                <div style="text-align: center; margin-top: 30px;">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <button type="submit" name="profile-edit-form" class="btn-primary">Update Profile</button>
                    <?php if ($editingSelf): ?>
                        <a href="viewProfile.php" class="btn-secondary" style="margin-left: 10px;">Cancel</a>
                    <?php else: ?>
                        <a href="viewProfile.php?id=<?php echo htmlspecialchars($_GET['id']) ?>" class="btn-secondary" style="margin-left: 10px;">Cancel</a>
                    <?php endif ?>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
