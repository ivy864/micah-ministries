<?php
    session_cache_expire(30);
    session_start();
    ini_set("display_errors",1);
    error_reporting(E_ALL);
    
    $loggedIn = false;
    $accessLevel = 0;
    $userID = null;
    if (isset($_SESSION['_id'])) {
        $loggedIn = true;
        $accessLevel = $_SESSION['access_level'];
        $userID = $_SESSION['_id'];
    }
    
    // require login
    if (!$loggedIn) {
        header('Location: login.php');
        die();
    }
    
    require_once('include/input-validation.php');
    require_once('domain/Person.php');
    require_once('database/dbPersons.php');
    
    $message = '';
    $error = '';
    $targetUser = null;
    $isAdminChangingOther = false;
    
    // check if admin is changing another user's password
    if (isset($_GET['user_id']) && $accessLevel >= 2) {
        $targetUserId = strtolower($_GET['user_id']);
        $targetUser = retrieve_person($targetUserId);
        
        if (!$targetUser) {
            $error = 'User not found';
        } else if ($targetUserId == 'vmsroot') {
            $error = 'Cannot modify root user password through this interface';
        } else if ($accessLevel == 2 && $targetUser->get_access_level() > 1) {
            $error = 'You do not have permission to modify this user\'s password';
        } else {
            $isAdminChangingOther = true;
        }
    } else {
        // user changing their own password
        $targetUser = retrieve_person($userID);
        $targetUserId = $userID;
    }
    
    // handle form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$error) {
        $requiredFields = ['new_password', 'confirm_password'];
        
        if ($isAdminChangingOther) {
            // admin changing other user's password - no current password required
            if (!wereRequiredFieldsSubmitted($_POST, $requiredFields)) {
                $error = 'All password fields are required';
            }
        } else {
            // user changing their own password - current password required
            $requiredFields[] = 'current_password';
            if (!wereRequiredFieldsSubmitted($_POST, $requiredFields)) {
                $error = 'All password fields are required';
            }
        }
        
        if (!$error) {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'];
            $confirmPassword = $_POST['confirm_password'];
            
            // validate current password for self-change
            if (!$isAdminChangingOther) {
                if (!password_verify($currentPassword, $targetUser->get_password())) {
                    $error = 'Current password is incorrect';
                }
            }
            
            // validate new password
            if (!$error) {
                if ($newPassword !== $confirmPassword) {
                    $error = 'New password and confirmation do not match';
                } else if ($newPassword === $currentPassword) {
                    $error = 'New password must be different from current password';
                } else {
                    // enhanced password validation
                    $passwordValidation = validatePasswordWithDetails($newPassword);
                    if ($passwordValidation !== true) {
                        $error = 'Password validation failed: ' . implode(', ', $passwordValidation);
                    } else {
                        // hash and update password
                        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
                        
                        if (change_password($targetUserId, $hashedPassword)) {
                            $message = 'Password updated successfully';
                            
                            // if admin changed another user's password, log the action
                            if ($isAdminChangingOther) {
                                // could add logging here if needed
                            }
                        } else {
                            $error = 'Failed to update password. Please try again.';
                        }
                    }
                }
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="images/micah-favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isAdminChangingOther ? 'Reset User Password' : 'Change Password'; ?> - Micah Ministries</title>
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
                <h1 class="main-text"><?php echo $isAdminChangingOther ? 'Reset User Password' : 'Change Password'; ?></h1>
                <div class="div-blue"></div>
                <p class="secondary-text">
                    <?php if ($isAdminChangingOther): ?>
                        Reset the password for <strong><?php echo htmlspecialchars($targetUser->get_first_name() . ' ' . $targetUser->get_last_name()); ?></strong>. The user will need to use this new password to log in.
                    <?php else: ?>
                        Update your account password. Make sure to choose a strong password that you can remember.
                    <?php endif; ?>
                </p>
                
                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <div class="form-container">
                    <form method="POST">
                        <?php if (!$isAdminChangingOther): ?>
                        <div class="form-group">
                            <label for="current_password">Current Password *</label>
                            <input type="password" id="current_password" name="current_password" required>
                        </div>
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="new_password">New Password *</label>
                            <input type="password" id="new_password" name="new_password" required>
                            <div class="password-help">
                                Password must be at least 8 characters with uppercase, lowercase, number, and special character
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password *</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <div style="text-align: center; margin-top: 30px;">
                            <button type="submit" class="blue-button">Update Password</button>
                            <a href="<?php echo $isAdminChangingOther ? 'viewProfile.php?id=' . urlencode($targetUserId) : 'viewProfile.php'; ?>" 
                               class="gray-button" style="margin-left: 10px;">Back to Profile</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
