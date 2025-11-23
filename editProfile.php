<?php
    // Author: Lauren Knight
    // Description: Profile edit page
    session_cache_expire(30);
    session_start();
    ini_set("display_errors",1);
    error_reporting(E_ALL);
    if (!isset($_SESSION['_id'])) {
        header('Location: login.php');
        die();
    }

    require_once('include/input-validation.php');

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["modify_access"]) && isset($_POST["id"])) {
        $id = $_POST['id'];
        header("Location: /gwyneth/modifyUserRole.php?id=$id");
    } else if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["profile-edit-form"])) {

        require_once('domain/Person.php');
        require_once('database/dbPersons.php');
        // make every submitted field SQL-safe except for password
        $ignoreList = array('password');
        $args = sanitize($_POST, $ignoreList);

        $editingSelf = true;
        if ($_SESSION['access_level'] >= 2 && isset($_POST['id'])) {
            $editingSelf = $_POST['id'] == $_SESSION['_id'];
            $id = $args['id'];
            // Check to see if user is a lower-level manager here
        } else {
            $id = $_SESSION['_id'];
        }

        // Get current person data to compare for changes
        require_once('database/dbPersons.php');
        $currentPerson = retrieve_person($id);
        if (!$currentPerson) {
            header('Location: viewProfile.php');
            die();
        }

        // echo "<p>The form was submitted:</p>";
        // foreach ($args as $key => $value) {
        //     echo "<p>$key: $value</p>";
        // }

        $required = array(
            'first_name', 'last_name', 'birthday', 'street_address', 'city', 'state',
            'zip_code', 'email', 'phone1', 'phone1type', 'emergency_contact_first_name',
            'emergency_contact_last_name', 'emergency_contact_phone',
            'emergency_contact_phone_type', 'emergency_contact_relation', 'user_role',
        );
        
        $errors = false;
        $errorDetails = array();

        if (!wereRequiredFieldsSubmitted($args, $required)) {
            $errors = true;
        }

        // Only admins (Level 3) can change user roles, first name, last name, and date of birth
        $isAdmin = $_SESSION['access_level'] >= 3;
        
        if ($isAdmin) {
            // Admins can change all fields
            $first_name = $args['first_name'];
            $last_name = $args['last_name'];
            $user_role = $args['user_role'];
            
            // Validate role
            if (!valueConstrainedTo($user_role, ['admin', 'case_manager', 'maintenance'])) {
                echo "<p>Invalid user role.</p>";
                $errors = true;
            }
            
            $birthday = validateDate($args['birthday']);
            if (!$birthday) {
                $errors = true;
                // echo 'bad dob';
            }
        } else {
            // Non-admins cannot change user role, first name, last name, or date of birth - keep existing values
            $first_name = $currentPerson->get_first_name();
            $last_name = $currentPerson->get_last_name();
            $user_role = $currentPerson->get_type();
            $birthday = $currentPerson->get_birthday();
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

        $email = validateEmail($args['email']);
        $allowedDomain = "dolovewalk.net";

        function emailAllowedDomain($email, $allowedDomain) {
            $domain = substr(strrchr($email, "@"), 1);
            return strtolower($domain) === strtolower($allowedDomain);
        }

        // Validate email format first
        if (!validateEmail($args['email'])) {
            $errors = true;
            $errorDetails['email'] = "Invalid email format!";
        }
        // Validate email domain (only if format is valid)
        else if (!emailAllowedDomain($args['email'], $allowedDomain)) {
            $errors = true;
            $errorDetails['email'] = "Invalid email domain! Please use a '$allowedDomain' email address.";
        }

        // Store the email after validation passes
        $email = $args['email'];
        
        $phone1 = validateAndFilterPhoneNumber($args['phone1']);
        if (!$phone1) {
            $errors = true;
            // echo 'bad phone';
        }

        $phone1type = $args['phone1type'];
        if (!valueConstrainedTo($phone1type, array('cellphone', 'home', 'work'))) {
            $errors = true;
            // echo 'bad phone type';
        }
        
        /*@
        $contactWhen = $args['contact-when'];
        $contactMethod = $args['contact-method'];
        if (!valueConstrainedTo($contactMethod, array('phone', 'text', 'email'))) {
            $errors = true;
            // echo 'bad contact method';
        }
        @*/

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

        /*@
        $gender = $args['gender'];
        if (!valueConstrainedTo($gender, ['Male', 'Female', 'Other'])) {
            $errors = true;
            echo 'bad gender';
        }
        @*/

       $skills = '';
       $interests = '';
        // Use user_role as type (admin, case_manager, maintenance)
        // This is set earlier in the code based on whether user is admin
        $type = $user_role;


       
        

        
        // For the new fields, default to 0 if not set
        
       
        if ($errors == true) {
            $updateSuccess = false;

        }
        else {
            $result = update_person_required(
            $id, $first_name, $last_name, $birthday, $street_address, $city, $state,
            $zip_code, $email, $phone1, $phone1type, $emergency_contact_first_name,
            $emergency_contact_last_name, $emergency_contact_phone,
            $emergency_contact_phone_type, $emergency_contact_relation,
            $user_role, $skills, $interests, 
            );

            if ($result) {
                if ($editingSelf) {
                    header('Location: viewProfile.php?editSuccess');
                } 
                else {
                    header('Location: viewProfile.php?editSuccess&id='. $id);
                }
                die();
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
    <title>Edit Profile - Micah Ministries</title>
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
                    <h1 style="margin: 0;" class="main-text">Edit Profile</h1>
                    <?php 
                    $backUrl = 'viewProfile.php';
                    if (isset($_GET['id']) && $_GET['id'] != $_SESSION['_id']) {
                        $backUrl = 'viewProfile.php?id=' . htmlspecialchars($_GET['id']);
                    }
                    ?>
                <div class="div-blue"></div>
                <p class="secondary-text">
                    Update user account information. Fill out the form below with the required information.
                </p>

                <div style="text-align:center; margin: 20px;">
                <a href="<?php echo $backUrl; ?>" class="gray-button" style="text-decoration: none; display: inline-block;">Back to Profile</a>
                </div>
            
                <?php
                    $isAdmin = $_SESSION['access_level'] >= 2;
                    require_once('profileEditForm.php');
                ?>
            </div>
        </div>
    </main>
</body>
</html>
