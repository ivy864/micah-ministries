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
    require_once('database/dbLeases.php');
    require_once('domain/Lease.php');
    
    $CMcon = connect();
    $CMquery = "SELECT first_name, last_name FROM dbpersons WHERE type = 'case_manager'";
    $CMresult = mysqli_query($CMcon, $CMquery);
    $CMcon->close();

    $message = '';
    $error = '';
    
    // handle form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Get form data
        $tenant_first_name = $_POST['tenant_first_name'] ?? '';
        $tenant_last_name = $_POST['tenant_last_name'] ?? '';
        $property_street = $_POST['property_street'] ?? '';
        $property_city = $_POST['property_city'] ?? '';
        $property_state = $_POST['property_state'] ?? '';
        $property_zip = $_POST['property_zip'] ?? '';
        $unit_number = $_POST['unit_number'] ?? '';
        $start_date = $_POST['start_date'] ?? '';
        $expiration_date = $_POST['expiration_date'] ?? '';
        $monthly_rent = $_POST['monthly_rent'] ?? '';
        $security_deposit = $_POST['security_deposit'] ?? '';
        $case_manager = $_POST['case_manager'] ?? '';
        $program_type = $_POST['program_type'] ?? '';
        
        // Handle file upload - THIS IS THE KEY FIX
        $lease_form = null;
        if (isset($_FILES['lease_form']) && $_FILES['lease_form']['error'] == UPLOAD_ERR_OK) {
            // Read the file contents as binary data
            $lease_form = file_get_contents($_FILES['lease_form']['tmp_name']);
            error_log("PDF uploaded successfully. Size: " . strlen($lease_form) . " bytes");
        } else {
            // Log why the file wasn't uploaded
            if (isset($_FILES['lease_form'])) {
                error_log("File upload error code: " . $_FILES['lease_form']['error']);
            } else {
                error_log("No file was uploaded");
            }
        }

        // basic validation
        if (empty($tenant_first_name) || empty($tenant_last_name) || empty($property_street) || 
            empty($unit_number) || empty($property_city) || empty($property_state) || 
            empty($property_zip) || empty($start_date) || empty($expiration_date) || empty($case_manager)) {
            $error = 'All tenant name fields, property address fields, unit number, start date, expiration date, and case manager are required.';
        } else {
            // generate unique lease id
            $lease_id = 'L' . date('YmdHis') . rand(100, 999);
            
            // create lease object
            $lease = new Lease(
                $lease_id, $tenant_first_name, $tenant_last_name, $property_street, 
                $unit_number, $property_city, $property_state, $property_zip,
                $start_date, $expiration_date, $monthly_rent, $security_deposit, $lease_form, $case_manager,
                $program_type, 'Active'
            );
            
            // add to database using object
            $result = add_lease($lease);
            
            if ($result) {
                $message = 'Lease created successfully! Lease ID: ' . $lease_id;
                // clear form data
                $_POST = array();
            } else {
                $error = 'Failed to create lease. Please try again.';
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>     <link rel="icon" type="image/png" href="images/micah-favicon.png">

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Lease</title>
  <link href="css/base.css" rel="stylesheet">

<!-- BANDAID FIX FOR HEADER BEING WEIRD -->
<?php
$tailwind_mode = true;
require_once('header.php');
?>
</head>

<body>

  <!-- Main Content -->
  <main>
    <div class="sections">

      <!-- Navigation Section - Removed -->
      <div class="button-section">
     </div>

      <!-- Text Section -->
      <div class="text-section">
        <h1 class="main-text">Create New Lease</h1>
        <div class="div-blue"></div>
        <p class="secondary-text">
          Submit a new lease agreement. Fill out the form below with all required information.
        </p>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="form-container">
            
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="tenant_first_name">Tenant First Name <span class="required">*</span></label>
                        <input type="text" id="tenant_first_name" name="tenant_first_name" 
                               value="<?php echo htmlspecialchars($_POST['tenant_first_name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="tenant_last_name">Tenant Last Name <span class="required">*</span></label>
                        <input type="text" id="tenant_last_name" name="tenant_last_name" 
                               value="<?php echo htmlspecialchars($_POST['tenant_last_name'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="property_street">Property Street Address <span class="required">*</span></label>
                        <input type="text" id="property_street" name="property_street" 
                               value="<?php echo htmlspecialchars($_POST['property_street'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="unit_number">Unit Number <span class="required">*</span></label>
                        <input type="text" id="unit_number" name="unit_number" 
                               value="<?php echo htmlspecialchars($_POST['unit_number'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="property_city">City <span class="required">*</span></label>
                        <input type="text" id="property_city" name="property_city" 
                               value="<?php echo htmlspecialchars($_POST['property_city'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="property_state">State <span class="required">*</span></label>
                        <input type="text" id="property_state" name="property_state" maxlength="2" 
                               value="<?php echo htmlspecialchars($_POST['property_state'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="property_zip">ZIP Code <span class="required">*</span></label>
                        <input type="text" id="property_zip" name="property_zip" 
                               value="<?php echo htmlspecialchars($_POST['property_zip'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <!-- Empty div for spacing -->
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="program_type">Program Type</label>
                        <select id="program_type" name="program_type">
                            <option value="">Select Program Type</option>
                            <option value="Program #1" <?php echo (($_POST['program_type'] ?? '') == 'Program #1') ? 'selected' : ''; ?>>Program #1</option>
                            <option value="Program #2" <?php echo (($_POST['program_type'] ?? '') == 'Program #2') ? 'selected' : ''; ?>>Program #2</option>
                            <option value="Emergency Housing" <?php echo (($_POST['program_type'] ?? '') == 'Emergency Housing') ? 'selected' : ''; ?>>Emergency Housing</option>
                            <option value="Transitional Housing" <?php echo (($_POST['program_type'] ?? '') == 'Transitional Housing') ? 'selected' : ''; ?>>Transitional Housing</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="monthly_rent">Monthly Rent Amount</label>
                        <input type="number" step="0.01" id="monthly_rent" name="monthly_rent" 
                               value="<?php echo htmlspecialchars($_POST['monthly_rent'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="start_date">Lease Start Date <span class="required">*</span></label>
                        <input type="date" id="start_date" name="start_date" 
                               value="<?php echo htmlspecialchars($_POST['start_date'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="expiration_date">Lease End Date <span class="required">*</span></label>
                        <input type="date" id="expiration_date" name="expiration_date" 
                               value="<?php echo htmlspecialchars($_POST['expiration_date'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="security_deposit">Security Deposit</label>
                        <input type="number" step="0.01" id="security_deposit" name="security_deposit" 
                               value="<?php echo htmlspecialchars($_POST['security_deposit'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="lease_form">Lease Form</label>
                        <input type="file" id="lease_form" name="lease_form" accept="application/pdf">
                    </div>
                    <div class="form-group">
                        <label for="case_manager">Case Manager Name <span class="required">*</span></label>
                        <select name="case_manager" id="case_manager" required>
                            <option value="">Select Case Manager</option>
                            <?php
                            if ($CMresult && $CMresult->num_rows > 0) {
                                while ($row = $CMresult->fetch_assoc()) {
                                    $fullName = htmlspecialchars($row['first_name'] . ' ' . $row['last_name']);
                                    $selected = (($_POST['case_manager'] ?? '') == $fullName) ? 'selected' : '';
                                    echo "<option value='" . $fullName . "' " . $selected . ">"
                                        . $fullName
                                        . "</option>";
                                }
                            } else {
                                echo "<option disabled>No case managers found</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <!-- Empty div for spacing -->
                    </div>
                </div>
                
                
                <div style="text-align: center; margin-top: 30px;">
                    <button type="submit" class="blue-button">Create Lease</button>
                    <a href="leaseman.php" class="gray-button" style="margin-left: 10px;">Cancel</a>
                </div>
            </form>
        </div>
        
      </div>

    </div>
  </main>

</body>
</html>