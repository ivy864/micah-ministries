<?php
    // Create New User Page - Admin Only Access
    // Based on VolunteerRegister.php but streamlined for staff

    session_cache_expire(30);
    session_start();

    $loggedIn = false;
    $accessLevel = 0;
    $userID = null;
    if (isset($_SESSION['_id'])) {
        $loggedIn = true;
        $accessLevel = $_SESSION['access_level'];
        $userID = $_SESSION['_id'];
    }
    
    // Admin-only access (level 3)
    if ($accessLevel < 3) {
        header('Location: index.php');
        die();
    }

    require_once('database/dbPersons.php');
    require_once('domain/Person.php');
    require_once('include/input-validation.php');

    $showPopup = false;
    $successMessage = '';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $ignoreList = array('password', 'password-reenter');
        $args = sanitize($_POST, $ignoreList);

        $required = array(
            'first_name', 'last_name', 'email', 'phone', 'username', 'password', 'user_role'
        );

        $errors = false;

        if (!wereRequiredFieldsSubmitted($args, $required)) {
            $errors = true;
        }

        $first_name = $args['first_name'];
        $last_name = $args['last_name'];
        $email = strtolower($args['email']);
        if (!validateEmail($email)) {
            echo "<p>Invalid email.</p>";
            $errors = true;
        }

        $phone1 = validateAndFilterPhoneNumber($args['phone']);
        if (!$phone1) {
            echo "<p>Invalid phone number.</p>";
            $errors = true;
        }

        $phone1type = 'cellphone'; // Default for staff
        $username = $args['username'];
        $user_role = $args['user_role'];
        
        // Validate role
        if (!valueConstrainedTo($user_role, ['admin', 'case_manager', 'maintenance'])) {
            echo "<p>Invalid user role.</p>";
            $errors = true;
        }

        $password = isSecurePassword($args['password']);
        if (!$password) {
            echo "<p>Password is not secure enough.</p>";
            $errors = true;
        } else {
            $password = password_hash($args['password'], PASSWORD_BCRYPT);
        }

        if ($errors) {
            echo '<p class="error">Your form submission contained unexpected or invalid input.</p>';
            die();
        }

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
            $showPopup = true;
        } else {
            $successMessage = "User '$username' has been successfully created with role: " . ucfirst(str_replace('_', ' ', $user_role));
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>     <link rel="icon" type="image/png" href="images/micah-favicon.png">

    <title>Micah Ministries | Create New User</title>
    <link href="css/normal_tw.css" rel="stylesheet">
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
    </style>
</head>
<body>

<header class="hero-header">
    <div class="center-header">
        <h1>Create New User</h1>
    </div>
</header>

<main>
    <div class="main-content-box w-[80%] p-8">

        <?php if ($successMessage): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <?php echo $successMessage; ?>
        </div>
        <?php endif; ?>

        <div class="text-center mb-8">
            <h2>Add New Staff Member</h2>
            <p class="sub-text">Create a new user account for staff members.</p>
        </div>

        <form id="create-user-form" class="space-y-6" method="post">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="first_name">First Name *</label>
                    <input type="text" id="first_name" name="first_name" class="w-full" required 
                           value="<?php if (isset($first_name)) echo htmlspecialchars($first_name); ?>" 
                           placeholder="Enter first name">
                </div>

                <div>
                    <label for="last_name">Last Name *</label>
                    <input type="text" id="last_name" name="last_name" class="w-full" required 
                           value="<?php if (isset($last_name)) echo htmlspecialchars($last_name); ?>" 
                           placeholder="Enter last name">
                </div>
            </div>

            <div>
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" class="w-full" required 
                       value="<?php if (isset($email)) echo htmlspecialchars($email); ?>" 
                       placeholder="Enter email address">
            </div>

            <div>
                <label for="phone">Phone Number *</label>
                <input type="tel" id="phone" name="phone" class="w-full" required 
                       value="<?php if (isset($phone1)) echo htmlspecialchars($phone1); ?>" 
                       placeholder="Enter phone number">
            </div>

            <div>
                <label for="username">Username *</label>
                <input type="text" id="username" name="username" class="w-full" required 
                       value="<?php if (isset($username)) echo htmlspecialchars($username); ?>" 
                       placeholder="Enter username (login ID)">
            </div>

            <div>
                <label for="user_role">User Role *</label>
                <select id="user_role" name="user_role" class="w-full" required>
                    <option value="">Select a role</option>
                    <option value="admin" <?php if (isset($user_role) && $user_role == 'admin') echo 'selected'; ?>>Admin - Full Access</option>
                    <option value="case_manager" <?php if (isset($user_role) && $user_role == 'case_manager') echo 'selected'; ?>>Case Manager - Lease + Maintenance</option>
                    <option value="maintenance" <?php if (isset($user_role) && $user_role == 'maintenance') echo 'selected'; ?>>Maintenance Staff - Maintenance Only</option>
                </select>
            </div>

            <div>
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" class="w-full" required 
                       placeholder="Enter secure password">
                <small class="text-gray-600">Password must be at least 8 characters with letters and numbers.</small>
            </div>

            <div class="text-center pt-4">
                <input type="submit" value="Create User" class="blue-button">
            </div>

        </form>
    </div>

    <div class="text-center mt-6">
        <a href="userman.php" class="return-button">Back to User Management</a>
    </div>

    <div class="info-section">
        <div class="blue-div"></div>
        <p class="info-text">
            Create new staff accounts with appropriate access levels. Admin users have full system access, 
            Case Managers can manage leases and maintenance, and Maintenance Staff can only access maintenance functions.
        </p>
    </div>
</main>

<?php if ($showPopup): ?>
<div id="popupMessage" class="absolute left-[40%] top-[20%] z-50 bg-red-800 p-4 text-white rounded-xl text-xl shadow-lg">
    That username is already taken.
</div>
<?php endif; ?>

<!-- Auto-hide popup -->
<script>
window.addEventListener('DOMContentLoaded', () => {
    const popup = document.getElementById('popupMessage');
    if (popup) {
        popup.style.transition = 'opacity 0.5s ease';
        setTimeout(() => {
            popup.style.opacity = '0';
            setTimeout(() => {
                popup.style.display = 'none';
            }, 500);
        }, 4000);
    }
});
</script>

</body>
</html>
