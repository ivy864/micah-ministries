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
        header('Location: micahportal.php');
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
  <link href="css/base.css?v=<?php echo time(); ?>" rel="stylesheet">

<!-- BANDAID FIX FOR HEADER BEING WEIRD -->
<?php
$tailwind_mode = true;
require_once('header.php');
?>

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
        <h1 class="main-text">Create New User</h1>
        <div class="div-blue"></div>
        <p class="secondary-text">
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

                <div style="text-align: center; margin-top: 30px;">
                    <button type="submit" class="blue-button">Create User</button>
                </div>
            </form>
        </div>
    </div>

    </div>
  </main>
</body>
</html>

