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

    // include database functions and domain object
    require_once('database/dbPersons.php');
    require_once('domain/Person.php');
    require_once('include/input-validation.php');
    
    $message = '';
    $error = '';
    
    // handle form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $ignoreList = array('password', 'password-reenter');
        $args = sanitize($_POST, $ignoreList);

        $required = array(
            'first_name', 'last_name', 'email', 'phone', 'username', 'password', 'user_role', 
            'birthday', 'street_address', 'city', 'state', 'zip_code', 'phone1type', 
            'emergency_contact_first_name', 'emergency_contact_last_name', 'emergency_contact_phone',
            'emergency_contact_phone_type', 'emergency_contact_relation'
        );

        $errors = false;
        $errorDetails = array();

        // Debug: Check what we received
        $errorDetails[] = "DEBUG: Received POST data: " . print_r($_POST, true);
        $errorDetails[] = "DEBUG: Sanitized args: " . print_r($args, true);

        if (!wereRequiredFieldsSubmitted($args, $required)) {
            $errors = true;
            $errorDetails[] = "DEBUG: Required fields check failed";
            foreach ($required as $field) {
                if (!isset($args[$field]) || empty($args[$field])) {
                    $errorDetails[] = "DEBUG: Missing or empty field: $field";
                }
            }
        }

        $first_name = $args['first_name'];
        $last_name = $args['last_name'];
        $email = strtolower($args['email']);
        $allowedDomain = "dolovewalk.net";

        function emailAllowedDomain($email, $allowedDomain) {
            $domain = substr(strrchr($email, "@"), 1);

            return strtolower($domain) === strtolower($allowedDomain);
        }

        $birthday = validateDate($args['birthday']);
        if (!$birthday) {
            $errors = true;
            // echo 'bad dob';
        }
        
        $street_address = $args['street_address'];

        $city = $args['city'];

        $state = $args['state'];
        if (!valueConstrainedTo($state, array('AK', 'AL', 'AR', 'AZ', 'CA', 'CO', 'CT', 'DC', 'DE', 'FL', 'GA',
                'HI', 'IA', 'ID', 'IL', 'IN', 'KS', 'KY', 'LA', 'MA', 'MD', 'ME',
                'MI', 'MN', 'MO', 'MS', 'MT', 'NC', 'ND', 'NE', 'NH', 'NJ', 'NM',
                'NV', 'NY', 'OH', 'OK', 'OR', 'PA', 'RI', 'SC', 'SD', 'TN', 'TX',
                'UT', 'VA', 'VT', 'WA', 'WI', 'WV', 'WY'))) {
            $errors = true;
        }

        $zip_code = $args['zip_code'];
        if (!validateZipcode($zip_code)) {
            $errors = true;
            // echo 'bad zip';
        }
        if (!validateEmail($email)) {
            $errorDetails[] = "DEBUG: Email validation failed for: $email";
            $errors = true;
        }

        if(!emailAllowedDomain($email, $allowedDomain)) {
            $errorDetails[] = "DEBUG: Email must be a '$allowedDomain' email address";
            $errors = true;
        }

        $phone1 = validateAndFilterPhoneNumber($args['phone']);
        if (!$phone1) {
            $errorDetails[] = "DEBUG: Phone validation failed for: " . $args['phone'];
            $errors = true;
        }

        $phone1type = $args['phone1type'];
        if (!valueConstrainedTo($phone1type, array('cellphone', 'home', 'work'))) {
            $errors = true;
            // echo 'bad phone type';
        }

        $emergency_contact_first_name = $args['emergency_contact_first_name'];
        
        $emergency_contact_last_name = $args['emergency_contact_last_name'];
        
        $emergency_contact_phone = validateAndFilterPhoneNumber($args['emergency_contact_phone']);
        if (!$emergency_contact_phone) {
            $errors = true;
            // echo 'bad e-contact phone';
        }

        $emergency_contact_phone_type = $args['emergency_contact_phone_type'];
        if (!valueConstrainedTo($emergency_contact_phone_type, array('cellphone', 'home', 'work'))) {
            $errors = true;
            // echo 'bad phone type';
        }

        $emergency_contact_relation = $args['emergency_contact_relation'];

        $username = $args['username'];
        $user_role = $args['user_role'];
        
        // Validate role
        if (!valueConstrainedTo($user_role, ['admin', 'case_manager', 'maintenance'])) {
            $errorDetails[] = "DEBUG: Role validation failed for: $user_role";
            $errors = true;
        }

        // Detailed password validation debugging
        $passwordInput = $args['password'];
        $passwordErrors = array();
        
        // Check if password is at least 8 characters long
        if (strlen($passwordInput) < 8) {
            $passwordErrors[] = "Password must be at least 8 characters long (current: " . strlen($passwordInput) . ")";
        }
        
        // Check if password contains at least one uppercase letter
        if (!preg_match('/[A-Z]/', $passwordInput)) {
            $passwordErrors[] = "Password must contain at least one uppercase letter";
        }
        
        // Check if password contains at least one lowercase letter
        if (!preg_match('/[a-z]/', $passwordInput)) {
            $passwordErrors[] = "Password must contain at least one lowercase letter";
        }
        
        // Check if password contains at least one number
        if (!preg_match('/[0-9]/', $passwordInput)) {
            $passwordErrors[] = "Password must contain at least one number";
        }
        
        if (!empty($passwordErrors)) {
            $errorDetails[] = "DEBUG: Password validation failed: " . implode(', ', $passwordErrors);
            $errors = true;
        } else {
            $password = password_hash($passwordInput, PASSWORD_BCRYPT);
        }

        if ($errors) {
            $error = 'Validation failed. Details: ' . implode(' | ', $errorDetails);
        } else {
            // Create new staff user with minimal required fields
            $newperson = new Person(
                $username, $password, date("Y-m-d"),
                $first_name, $last_name, $birthday,
                $street_address, $city, $state, $zip_code,
                $phone1, $phone1type, $email,
                $emergency_contact_first_name, $emergency_contact_last_name, $emergency_contact_phone,
                $emergency_contact_phone_type, $emergency_contact_relation,
                $user_role, 'Active', 0, // Active status, not archived
                '', '', 'None', // No skills, interests, training
                0, 0, 0.00 // Not volunteer-related fields
            );

            $result = add_person($newperson);
            if (!$result) {
                $error = 'That username is already taken.';
            } else {
                $message = "User '$username' has been successfully created with role: " . ucfirst(str_replace('_', ' ', $user_role));
                // clear form data
                $_POST = array();
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>     <link rel="icon" type="image/png" href="images/micah-favicon.png">

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create New User</title>
  <link href="css/management_tw.css?v=<?php echo time(); ?>" rel="stylesheet">

<!-- BANDAID FIX FOR HEADER BEING WEIRD -->
<?php
$tailwind_mode = true;
require_once('header.php');
?>
<style>
        .date-box {
            background: #274471;
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
	
	.form-container {
	    max-width: none !important;
	    width: 100% !important;
	    margin: 5px auto !important;
	    padding: 10px !important;
	    background: white !important;
	    border-radius: 8px !important;
	    box-shadow: 0 4px 8px rgba(0,0,0,0.15) !important;
	    border: 2px solid #274471 !important;
	}
	
	.form-group {
	    margin-bottom: 6px !important;
	}
	
	.form-group label {
	    display: block;
	    margin-bottom: 5px;
	    font-weight: bold;
	    color: #274471;
	}
	
	.form-group input,
	.form-group select,
	.form-group textarea {
	    width: 100%;
	    padding: 8px;
	    border: 1px solid #ddd;
	    border-radius: 4px;
	    font-size: 14px;
	}
	
	.form-group textarea {
	    height: 40px;
	    resize: vertical;
	}
	
	.form-row {
	    display: flex;
	    gap: 20px;
	}
	
	.form-row .form-group {
	    flex: 1;
	}
	
	/* compact navigation buttons - override tailwind */
	.button-section {
	    margin-bottom: 10px !important;
	}
	
	.compact-nav-btn {
	    padding: 8px 16px !important;
	    font-size: 14px !important;
	    margin-right: 10px !important;
	    margin-bottom: 5px !important;
	    height: auto !important;
	    width: auto !important;
	}
	
	.compact-nav-btn .button-left-gray {
	    padding: 4px 8px !important;
	}
	
	.compact-nav-btn .button-icon {
	    height: 16px !important;
	    width: 16px !important;
	    left: 12px !important;
	}
	
	.btn-primary {
	    background-color: #274471;
	    color: white;
	    padding: 10px 20px;
	    border: none;
	    border-radius: 4px;
	    cursor: pointer;
	    font-size: 16px;
	}
	
	.btn-primary:hover {
	    background-color: #1e3554;
	}
	
	.btn-secondary {
	    background-color: #6c757d;
	    color: white;
	    padding: 10px 20px;
	    border: none;
	    border-radius: 4px;
	    cursor: pointer;
	    font-size: 16px;
	    text-decoration: none;
	    display: inline-block;
	}
	
	.btn-secondary:hover {
	    background-color: #5a6268;
	}
	
	.alert {
	    padding: 15px;
	    margin-bottom: 20px;
	    border-radius: 4px;
	}
	
	.alert-success {
	    background-color: #d4edda;
	    color: #155724;
	    border: 1px solid #c3e6cb;
	}
	
	.alert-error {
	    background-color: #f8d7da;
	    color: #721c24;
	    border: 1px solid #f5c6cb;
	}
	
	/* compact text section */
	.text-section h1 {
	    margin-bottom: 5px !important;
	}
	
	.text-section p {
	    margin-bottom: 10px !important;
	}
	
	/* force layout changes - full width form */
	.sections {
	    flex-direction: row !important;
	    gap: 10px !important;
	}

    .section-box {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        background: #f9f9f9;
    }
    
    .section-box h3 {
        color: #274471;
        margin-bottom: 15px;
        text-align: center;
    }
	
	/* adjust main content since hero is removed */
	main {
	    margin-top: 0 !important;
	    padding: 10px !important;
	}
	
	.button-section {
	    width: 0% !important;
	    display: none !important;
	}
	
	.text-section {
	    width: 100% !important;
	}
	
	.form-container {
	    max-width: none !important;
	    width: 100% !important;
	}
	
	.password-help {
	    font-size: 12px;
	    color: #666;
	    margin-top: 5px;
	}

    .radio-group {
        display: flex;
        gap: 15px;
        margin-top: 5px;
    }

    .radio-group input[type="radio"] {
        width: auto;
        margin-right: 5px;
    }
    
    .radio-group label {
        margin-bottom: 0;
        font-weight: normal;
        color: #333;
    }


</style>
<!-- BANDAID END, REMOVE ONCE SOME GENIUS FIXES -->

</head>

<body>

  <!-- Hero Section - Removed to save space -->
  <!-- <header class="hero-header"></header> -->

  <!-- Main Content -->
  <main>
    <div class="sections">

      <!-- Navigation Section - Removed -->
      <div class="button-section">
     </div>

      <!-- Text Section -->
      <div class="text-section">
        <h1>Create New User</h1>
        <div class="div-blue"></div>
        <p>
          Create a new user account for staff members. Fill out the form below with all required information.
        </p>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form id="create-user-form" method="post">

                <div class="section-box">
                    <h3>Personal Information</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name *</label>
                                <input type="text" id="first_name" name="first_name" required 
                                    value="<?php if (isset($first_name)) echo htmlspecialchars($first_name); ?>" 
                                    placeholder="Enter first name">
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" required 
                                    value="<?php if (isset($last_name)) echo htmlspecialchars($last_name); ?>" 
                                    placeholder="Enter last name">
                            </div>
                        </div>

                        <div class="form-group">
                                <label for="birthday">Date of Birth *</label>
                                <input type="date" id="birthday" name="birthday" required 
                                    value="<?php if (isset($birthday)) echo htmlspecialchars($birthday); ?>">
                        </div>

                        <div class="form-group">
                                <label for="street_address">Street Address *</label>
                                <input type="text" id="street_address" name="street_address" required 
                                    value="<?php if (isset($street_address)) echo htmlspecialchars($street_address); ?>" 
                                    placeholder="Enter your street address">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="city">City *</label>
                                <input type="text" id="city" name="city" required 
                                    value="<?php if (isset($city)) echo htmlspecialchars($city); ?>" 
                                    placeholder="Enter your city">
                            </div>

                            <div class="form-group">
                                <label for="state">State *</label>
                                <select id="state" name="state" required>
                                            <?php
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
                                <input type="text" id="zip_code" name="zip_code"  
                                pattern="[0-9]{5}" title="5-digit zip code" required value="<?php if (isset($zip_code)) echo htmlspecialchars($zip_code); ?>"
                                placeholder="Enter your zip code">
                        </div>
                </div>
                <div class="section-box">
                    <h3>Contact Information</h3>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required 
                            value="<?php if (isset($email)) echo htmlspecialchars($email); ?>" 
                            placeholder="Enter email address">
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="tel" id="phone" name="phone" required 
                            value="<?php if (isset($phone1)) echo htmlspecialchars($phone1); ?>" 
                            placeholder="Enter phone number">
                    </div>

                    <div class="form-group">
                        <label>Phone Type *</label>
                        <div class="radio-group">
                            <input type="radio" id="phone-type-cellphone" name="phone1type" value="cell" 
                                <?php if (isset($phone1type) && $phone1type === 'cell') echo 'checked'; ?> required>
                            <label for="phone-type-cellphone">Cell</label>

                            <input type="radio" id="phone-type-home" name="phone1type" value="home" 
                                <?php if (isset($phone1type) && $phone1type === 'home') echo 'checked'; ?> required>
                            <label for="phone-type-home">Home</label>

                            <input type="radio" id="phone-type-work" name="phone1type" value="work" 
                                <?php if (isset($phone1type) && $phone1type === 'work') echo 'checked'; ?> required>
                            <label for="phone-type-work">Work</label>
                        </div>

                    </div>
                </div>

                <div class="section-box">
                    <h3>Emergency Contact Information</h3>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="emergency_contact_first_name">Emergency Contact First Name *</label>
                            <input type="text" id="emergency_contact_first_name" name="emergency_contact_first_name" 
                            value="<?php if(isset($emergency_contact_first_name)) echo htmlspecialchars($emergency_contact_first_name); ?>" required
                            placeholder="Enter First Name">
                        </div>
                        <div class="form-group">
                            <label for="emergency_contact_last_name">Emergency Contact Last Name *</label>
                            <input type="text" id="emergency_contact_last_name" name="emergency_contact_last_name" 
                            value="<?php if(isset($emergency_contact_last_name)) echo htmlspecialchars($emergency_contact_last_name); ?>" required
                            placeholder="Enter Last Name">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="emergency_contact_phone">Emergency Contact Phone *</label>
                        <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone" value="<?php if(isset($emergency_contact_phone)) echo htmlspecialchars($emergency_contact_phone); ?>" required
                        placeholder="Enter phone number">
                    </div>

                    <div class="form-group">
                        <label>Emergency Contact Phone Type *</label>
                        <div class="radio-group">
                            <input type="radio" id="emergency-phone-type-cellphone" name="emergency_contact_phone_type" value="cell" 
                                <?php if (isset($emergency_contact_phone_type) && $emergency_contact_phone_type === 'cell') echo 'checked'; ?> required>
                            <label for="phone-type-cellphone">Cell</label>

                            <input type="radio" id="emergency-phone-type-home" name="emergency_contact_phone_type" value="home" 
                                <?php if (isset($emergency_contact_phone_type) && $emergency_contact_phone_type === 'home') echo 'checked'; ?> required>
                            <label for="phone-type-home">Home</label>

                            <input type="radio" id="emergency-phone-type-work" name="emergency_contact_phone_type" value="work" 
                                <?php if (isset($emergency_contact_phone_type) && $emergency_contact_phone_type === 'work') echo 'checked'; ?> required>
                            <label for="phone-type-work">Work</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="emergency_contact_relation">Emergency Contact Relation *</label>
                        <input type="text" id="emergency_contact_relation" name="emergency_contact_relation" 
                        value="<?php if(isset($emergency_contact_relation)) echo htmlspecialchars($emergency_contact_relation); ?>" required
                        placeholder="Enter Relationship Type">
                    </div>
                </div>

                <div class="section-box">
                    <h3>Login Credentials</h3>
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" id="username" name="username" required 
                            value="<?php if (isset($username)) echo htmlspecialchars($username); ?>" 
                            placeholder="Enter username (login ID)">
                    </div>

                    <div class="form-group">
                        <label for="user_role">User Role *</label>
                        <select id="user_role" name="user_role" required>
                            <option value="">Select a role</option>
                            <option value="admin" <?php if (isset($user_role) && $user_role == 'admin') echo 'selected'; ?>>Admin - Full Access</option>
                            <option value="case_manager" <?php if (isset($user_role) && $user_role == 'case_manager') echo 'selected'; ?>>Case Manager - Lease + Maintenance</option>
                            <option value="maintenance" <?php if (isset($user_role) && $user_role == 'maintenance') echo 'selected'; ?>>Maintenance Staff - Maintenance Only</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" required 
                            placeholder="Enter secure password">
                        <div class="password-help">
                            Password must be at least 8 characters with uppercase, lowercase, number, and special character
                        </div>
                    </div>
                </div>

                

                <div style="text-align: center; margin-top: 30px;">
                    <button type="submit" class="btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>

    </div>
  </main>
</body>
</html>

