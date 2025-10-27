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
            $id = $_POST['id'];
            $editingSelf = $id == $_SESSION['_id'];
            $id = $args['id'];
            // Check to see if user is a lower-level manager here
        } else {
            $id = $_SESSION['_id'];
        }

        // echo "<p>The form was submitted:</p>";
        // foreach ($args as $key => $value) {
        //     echo "<p>$key: $value</p>";
        // }

        $required = array(
            'first_name', 'last_name', 'birthday', 'street_address', 'city', 'state',
            'zip_code', 'email', 'phone1', 'phone1type', 'emergency_contact_first_name',
            'emergency_contact_last_name', 'emergency_contact_phone',
            'emergency_contact_phone_type', 'emergency_contact_relation', 'user_role'
        );
        $errors = false;
        if (!wereRequiredFieldsSubmitted($args, $required)) {
            $errors = true;
        }

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
        if (!$email) {
            $errors = true;
            // echo 'bad email';
        }

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

       $type = 'v';


       
        

        
        $skills = $args['skills'];
        $interests = $args['interests'];
        
       
        // For the new fields, default to 0 if not set
        
       
        if ($errors) {
            $updateSuccess = false;
        }
        
        $result = update_person_required(
            $id, $first_name, $last_name, $birthday, $street_address, $city, $state,
            $zip_code, $email, $phone1, $phone1type, $emergency_contact_first_name,
            $emergency_contact_last_name, $emergency_contact_phone,
            $emergency_contact_phone_type, $emergency_contact_relation, $type,
             $skills, $interests, $user_role
        );
        if ($result) {
            if ($editingSelf) {
                header('Location: viewProfile.php?editSuccess');
            } else {
                header('Location: viewProfile.php?editSuccess&id='. $id);
            }
            die();
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
    
    fieldset {
        border: none;
        margin: 0;
        padding: 0;
    }
    
    legend {
        font-weight: bold;
        color: #274471;
        margin-bottom: 10px;
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
                <h1>Edit Profile</h1>
                <div class="div-blue"></div>
                <p>
                    Update user account information. Fill out the form below with the required information.
                </p>
                
                <?php
                    $isAdmin = $_SESSION['access_level'] >= 2;
                    require_once('profileEditForm.php');
                ?>
            </div>
        </div>
    </main>
</body>
</html>
