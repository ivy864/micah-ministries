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
            'first_name', 'last_name', 'email', 'phone', 'username', 'password', 'user_role'
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
        if (!validateEmail($email)) {
            $errorDetails[] = "DEBUG: Email validation failed for: $email";
            $errors = true;
        }

        $phone1 = validateAndFilterPhoneNumber($args['phone']);
        if (!$phone1) {
            $errorDetails[] = "DEBUG: Phone validation failed for: " . $args['phone'];
            $errors = true;
        }

        $phone1type = 'cellphone'; // Default for staff
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
                $first_name, $last_name, '1990-01-01', // Default birthday
                'N/A', 'N/A', 'VA', '00000', // Default address
                $phone1, $phone1type, $email,
                'N/A', 'N/A', '0000000000', 'cellphone', 'N/A', // Default emergency contact
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
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" 
                               value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" 
                               value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="tel" id="phone" name="phone" 
                               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" id="username" name="username" 
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="user_role">User Role *</label>
                        <select id="user_role" name="user_role" required>
                            <option value="">Select a role</option>
                            <option value="admin" <?php echo (($_POST['user_role'] ?? '') == 'admin') ? 'selected' : ''; ?>>Admin - Full Access</option>
                            <option value="case_manager" <?php echo (($_POST['user_role'] ?? '') == 'case_manager') ? 'selected' : ''; ?>>Case Manager - Lease + Maintenance</option>
                            <option value="maintenance" <?php echo (($_POST['user_role'] ?? '') == 'maintenance') ? 'selected' : ''; ?>>Maintenance Staff - Maintenance Only</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required>
                    <small style="color: #666; font-size: 12px;">Password must be at least 8 characters with letters and numbers.</small>
                </div>
                
                <div style="text-align: center; margin-top: 30px;">
                    <button type="submit" class="btn-primary">Create User</button>
                    <a href="personSearch.php" class="btn-secondary" style="margin-left: 10px;">Cancel</a>
                </div>
            </form>
        </div>
        
      </div>

    </div>
  </main>
</body>
</html>

