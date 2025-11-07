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

    // include database functions
    require_once('database/dbinfo.php');
    
    $message = '';
    $error = '';
    
    // handle form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
        $program_type = $_POST['program_type'] ?? '';
        
        // basic validation
        if (empty($tenant_first_name) || empty($tenant_last_name) || empty($property_street) || empty($unit_number) || empty($property_city) || empty($property_state) || empty($property_zip) || empty($start_date) || empty($expiration_date)) {
            $error = 'All tenant name fields, property address fields, unit number, start date, and expiration date are required.';
        } else {
            // connect to database
            $con = connect();
            
            if ($con) {
                // generate unique lease id
                $lease_id = 'L' . date('YmdHis') . rand(100, 999);
                
                // insert lease into database
                $query = "INSERT INTO dbleases (id, tenant_first_name, tenant_last_name, property_street, unit_number, property_city, property_state, property_zip, start_date, expiration_date, monthly_rent, security_deposit, program_type, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                
                $stmt = mysqli_prepare($con, $query);
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "sssssssssssss", 
                        $lease_id, $tenant_first_name, $tenant_last_name, $property_street, $unit_number, $property_city, $property_state, $property_zip, 
                        $start_date, $expiration_date, $monthly_rent, $security_deposit, $program_type);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $message = 'Lease created successfully! Lease ID: ' . $lease_id;
                        // clear form data
                        $_POST = array();
                    } else {
                        $error = 'Failed to create lease. Please try again.';
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $error = 'Database error. Please try again.';
                }
                mysqli_close($con);
            } else {
                $error = 'Database connection failed. Please try again.';
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
  <link href="css/management_tw.css?v=<?php echo time(); ?>" rel="stylesheet">

<!-- BANDAID FIX FOR HEADER BEING WEIRD -->
<?php
$tailwind_mode = true;
require_once('header.php');
?>
<style>
        .form-container {
            max-width: none !important;
            width: 100% !important;
            margin: 0;
            padding: 0;
            background: transparent;
            border-radius: 0;
            box-shadow: none;
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
        
        .form-row {
            display: flex;
            gap: 40px;
            margin-bottom: 25px;
        }
        
        .form-group {
            flex: 1;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
            font-size: 14px;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        
        .form-group textarea {
            height: 100px;
            resize: vertical;
        }
        
        .form-buttons {
            text-align: center;
            margin-top: 30px;
        }
        
        .btn-primary {
            background: #274471;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-right: 15px;
        }
        
        .btn-primary:hover {
            background: #1e3554;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .required {
            color: #dc3545;
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
        <h1>Create New Lease</h1>
        <div class="div-blue"></div>
        <p>
          Submit a new lease agreement. Fill out the form below with all required information.
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
                        <!-- Empty div for spacing -->
                    </div>
                    <div class="form-group">
                        <!-- Empty div for spacing -->
                    </div>
                </div>
                
                
                <div style="text-align: center; margin-top: 30px;">
                    <button type="submit" class="btn-primary">Create Lease</button>
                    <a href="leaseman.php" class="btn-secondary" style="margin-left: 10px;">Cancel</a>
                </div>
            </form>
        </div>
        
      </div>

    </div>
  </main>
</body>
</html>
