<?php
    // Template for new VMS pages. Base your new page on this one

    // Make session information accessible, allowing us to associate
    // data with the logged-in user.
    session_cache_expire(30);
    session_start();
    ini_set("display_errors",1);
    error_reporting(E_ALL);
    $loggedIn = false;
    $accessLevel = 0;
    $userID = null;
    if (isset($_SESSION['_id'])) {
        $loggedIn = true;
        // 0 = not logged in, 1 = standard user, 2 = manager (Admin), 3 super admin (TBI)
        $accessLevel = $_SESSION['access_level'];
        $userID = $_SESSION['_id'];
    }
    require_once('include/input-validation.php');
    
    $get = sanitize($_GET);
    $id = $get['id'];
    // Does the person exist?
    require_once('domain/Person.php');
    require_once('database/dbPersons.php');
    $thePerson = retrieve_person($id);
    if (!$thePerson) {
        echo "That user does not exist";
        die();
    }
    
    // Is user authorized to view this page?
    if ($accessLevel < 2) {
        header('Location: micahportal.php');
        die();
    }
    // Was an ID supplied?
    if ($_SERVER["REQUEST_METHOD"] == "GET" && !isset($_GET['id'])) {
        header('Location: micahportal.php');
        die();
    } else if ($_SERVER["REQUEST_METHOD"] == "POST"){
        require_once('database/dbPersons.php');
        require_once('database/dbMessages.php');
        $post = sanitize($_POST);
        $new_role = $post['s_role'];
        if (!valueConstrainedTo($new_role, ['admin', 'case_manager', 'maintenance'])) {
            die();
        }
        if (empty($new_role)){
            // echo "No new role selected";
        }else if ($accessLevel >= 3) {
            update_type($id, $new_role);
            $typeChange = true;
            // echo "<meta http-equiv='refresh' content='0'>";
        }
        $new_status = $post['statsRadio'];
        if (!valueConstrainedTo($new_status, ['Active', 'Inactive'])) {
            die();
        }
        if (empty($new_status)){
            // echo "No new status selected";
        }else{
            update_status($id, $new_status);
            $statusChange = true;
            // echo "<meta http-equiv='refresh' content='0'>";
        }

        $currentStatus = $thePerson->get_status();
            
        if (isset($_POST['user_access_modified'])) { // Check if the form was submitted
            $newStatus = $_POST['statsRadio']; // Get the selected status
            if ($newStatus == "Inactive" && $currentStatus != "Inactive") {
                // Notify Admins about archived volunteers - Implemented by Aidan Meyer 

                $archive_title = $thePerson->get_id() . " has been archived.";
                $archive_message = "This user has been archived. For reinstatement, navigate to volunteer search and select Archive, then modify the field to Active";
                system_message_all_admins($archive_title, $archive_message);
            }
        }
        if (isset($notesChange) || isset($statusChange) || isset($typeChange)) {
            header('Location: viewProfile.php?rscSuccess&id=' . $_GET['id']);
            die();
        }
    }

    // make every submitted field SQL-safe except for password
    $ignoreList = array('password');
    $args = sanitize($_POST);
?>
<!DOCTYPE html>
<html>
    <head>     <link rel="icon" type="image/png" href="images/micah-favicon.png">

        <?php require_once('universal.inc') ?>
        <title>Micah Ministries | Modify User Role</title>
        <style>
            .modUser{
                display: flex;
                flex-direction: column;
                gap: .5rem;
                padding: 0 0 4rem 0;
            }
            main.user-role {
                gap: 1rem;
                display: flex;
                flex-direction: column;
            }
            @media only screen and (min-width: 1024px) {
                .modUser {
                    width: 100%;
                }
                main.user-role {
                    /* align-items: center; */
                    margin: 0rem 16rem;
                    /* width: 50rem; */
                }
            }
        </style>
    </head>
    <body>
        <?php require_once('header.php') ?>
        <h1>Modify User Role and Status</h1>
        <main class="user-role">
            <?php if ($accessLevel == 3): ?>
                <h2>Modify <?php echo $thePerson->get_first_name() . " " . $thePerson->get_last_name(); ?>'s Role and Status</h2>
            <?php else: ?>
                <h2>Modify <?php echo $thePerson->get_first_name() . " " . $thePerson->get_last_name(); ?>'s Status</h2>
            <?php endif ?>
            <form class="modUser" method="post">
                <?php if (isset($typeChange) || isset($notesChange) || isset($statusChange)): ?>
                    <div class="happy-toast">User's access is updated.</div>
                <?php endif ?>
                    <?php
                        // Provides drop down of the role types to select and change the role
			//other than the person's current role type is displayed
            if ($accessLevel == 3) {
				$roles = array(
                    'admin' => 'Admin (Lease + Maintenance + User Management)', 
                    'case_manager' => 'Case Manager (Lease + Maintenance)', 
                    'maintenance' => 'Maintenance Staff (Maintenance Only)'
                );
                echo '<label for="role">Change Role</label><select id="role" class="form-select-sm" name="s_role">' ;
                // echo '<option value="" SELECTED></option>' ;
                $currentRole = $thePerson->get_type();
                foreach ($roles as $role => $typename) {
                    if($role != $currentRole) {
                        echo '<option value="'. $role .'">'. $typename .'</option>';
                    } else {
                        echo '<option value="'. $role .'" selected>'. $typename .' (current)</option>';
                    }
                }
                echo '</select>';
            }
        ?>
		<label>Change Status</label>
		<div class="form-row">
            <?php
                // Check the person's status and check the radio to signal the current status
                // Display the current and other available statuses as well to change the status
		        $currentStatus = $thePerson->get_status();
                if ($currentStatus == "Active") {
                    echo '<input type="radio" name="statsRadio" id = "makeActive" value="Active" checked><label for="makeActive" class="checkbox-label">Active</label>';
                    echo '<input type="radio" name="statsRadio" id = "makeInactive" value="Inactive"><label for="makeInactive" class="checkbox-label">Inactive</label>';
                } elseif ($currentStatus == "Inactive") {
                    echo '<input type="radio" name="statsRadio" id = "makeActive" value="Active"><label for="makeActive" class="checkbox-label">Active</label>';
                    echo '<input type="radio" name="statsRadio" id = "makeInactive" value="Inactive" checked><label for="makeInactive" class="checkbox-label">Inactive</label>';
                }
		    ?>
		</div>
	

                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <input type="submit" name="user_access_modified" value="Update">
                <a class="button cancel" href="viewProfile.php?id=<?php echo htmlspecialchars($_GET['id']) ?>">Cancel</a>
		</form>
        </main>
    </body>
</html>
