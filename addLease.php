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

    // include database functions
    require_once('database/dbinfo.php');
    
    $message = '';
    $error = '';
    
    // handle form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $tenant_name = $_POST['tenant_name'] ?? '';
        $property_address = $_POST['property_address'] ?? '';
        $unit_number = $_POST['unit_number'] ?? '';
        $start_date = $_POST['start_date'] ?? '';
        $expiration_date = $_POST['expiration_date'] ?? '';
        $monthly_rent = $_POST['monthly_rent'] ?? '';
        $security_deposit = $_POST['security_deposit'] ?? '';
        $program_type = $_POST['program_type'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        // basic validation
        if (empty($tenant_name) || empty($property_address) || empty($start_date) || empty($expiration_date)) {
            $error = 'Tenant name, property address, start date, and expiration date are required.';
        } else {
            // connect to database
            $con = connect();
            
            if ($con) {
                // generate unique lease id
                $lease_id = 'L' . date('YmdHis') . rand(100, 999);
                
                // insert lease into database
                $query = "INSERT INTO leases (id, tenant_name, property_address, unit_number, start_date, expiration_date, monthly_rent, security_deposit, program_type, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                
                $stmt = mysqli_prepare($con, $query);
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "ssssssssss", 
                        $lease_id, $tenant_name, $property_address, $unit_number, 
                        $start_date, $expiration_date, $monthly_rent, $security_deposit, 
                        $program_type, $notes);
                    
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
            max-width: 1200px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-section {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: calc(100vh - 200px);
            padding: 20px;
        }
        
        .form-header {
            margin-bottom: 30px;
            text-align: left;
        }
        
        .form-header h1 {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        
        .form-header p {
            font-size: 16px;
            color: #666;
            margin: 0;
        }
        
        .form-row {
            display: flex;
            gap: 30px;
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
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
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

  <!-- Larger Hero Section -->
  <header class="hero-header"></header>


  <!-- Main Content -->
  <main>
    <div class="sections">

      <!-- Form Section -->
      <div class="form-section">
        
        <?php if ($message): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <div class="form-container">
            <div class="form-header">
                <h1>Create New Lease</h1>
                <p>Submit a new lease agreement. Fill out the form below with all required information.</p>
            </div>
            
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="tenant_name">Tenant Name <span class="required">*</span></label>
                        <input type="text" id="tenant_name" name="tenant_name" 
                               value="<?php echo htmlspecialchars($_POST['tenant_name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="property_address">Property Address <span class="required">*</span></label>
                        <input type="text" id="property_address" name="property_address" 
                               value="<?php echo htmlspecialchars($_POST['property_address'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="unit_number">Unit Number</label>
                        <input type="text" id="unit_number" name="unit_number" 
                               value="<?php echo htmlspecialchars($_POST['unit_number'] ?? ''); ?>">
                    </div>
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
                        <label for="monthly_rent">Monthly Rent Amount</label>
                        <input type="number" step="0.01" id="monthly_rent" name="monthly_rent" 
                               value="<?php echo htmlspecialchars($_POST['monthly_rent'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="security_deposit">Security Deposit</label>
                        <input type="number" step="0.01" id="security_deposit" name="security_deposit" 
                               value="<?php echo htmlspecialchars($_POST['security_deposit'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="notes">Additional Notes</label>
                    <textarea id="notes" name="notes"><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-buttons">
                    <button type="submit" class="btn-primary">Create Lease</button>
                    <a href="leaseman.php" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
        
      </div>

    </div>
  </main>
</body>
</html>
