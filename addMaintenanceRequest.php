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
    require_once('database/dbMaintenanceRequests.php');
    require_once('domain/MaintenanceRequest.php');
    
    $message = '';
    $error = '';
    
    // handle form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $id = 'MR' . date('YmdHis') . rand(100, 999); // generate unique ID
        $requester_name = $_POST['requester_name'] ?? '';
        $requester_email = $_POST['requester_email'] ?? '';
        $requester_phone = $_POST['requester_phone'] ?? '';
        $location = $_POST['location'] ?? '';
        $building = $_POST['building'] ?? '';
        $unit = $_POST['unit'] ?? '';
        $description = $_POST['description'] ?? '';
        $priority = $_POST['priority'] ?? 'Medium';
        $assigned_to = $_POST['assigned_to'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        // basic validation
        if (empty($requester_name) || empty($description)) {
            $error = 'Requester name and description are required.';
        } else {
            // create maintenance request object
            $maintenanceRequest = new MaintenanceRequest(
                $id, $requester_name, $requester_email, $requester_phone, 
                $location, $building, $unit, $description, $priority, 
                'Pending', $assigned_to, $notes
            );
            
            // add to database using object
            $result = add_maintenance_request($maintenanceRequest);
            
            if ($result) {
                $message = 'Maintenance request created successfully! Request ID: ' . $id;
                // clear form data
                $_POST = array();
            } else {
                $error = 'Failed to create maintenance request. Please try again.';
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Maintenance Request</title>
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
        <h1>Create Maintenance Request</h1>
        <div class="div-blue"></div>
        <p>
          Submit a new maintenance request. Fill out the form below with all required information.
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
                        <label for="requester_name">Requester Name *</label>
                        <input type="text" id="requester_name" name="requester_name" 
                               value="<?php echo htmlspecialchars($_POST['requester_name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="requester_email">Email</label>
                        <input type="email" id="requester_email" name="requester_email" 
                               value="<?php echo htmlspecialchars($_POST['requester_email'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="requester_phone">Phone</label>
                        <input type="tel" id="requester_phone" name="requester_phone" 
                               value="<?php echo htmlspecialchars($_POST['requester_phone'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="priority">Priority</label>
                        <select id="priority" name="priority">
                            <option value="Low" <?php echo (($_POST['priority'] ?? '') == 'Low') ? 'selected' : ''; ?>>Low</option>
                            <option value="Medium" <?php echo (($_POST['priority'] ?? '') == 'Medium') ? 'selected' : ''; ?>>Medium</option>
                            <option value="High" <?php echo (($_POST['priority'] ?? '') == 'High') ? 'selected' : ''; ?>>High</option>
                            <option value="Emergency" <?php echo (($_POST['priority'] ?? '') == 'Emergency') ? 'selected' : ''; ?>>Emergency</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="location">Location</label>
                        <input type="text" id="location" name="location" 
                               value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="building">Building</label>
                        <input type="text" id="building" name="building" 
                               value="<?php echo htmlspecialchars($_POST['building'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="unit">Unit</label>
                        <input type="text" id="unit" name="unit" 
                               value="<?php echo htmlspecialchars($_POST['unit'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="assigned_to">Assign To</label>
                        <input type="text" id="assigned_to" name="assigned_to" 
                               value="<?php echo htmlspecialchars($_POST['assigned_to'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="notes">Notes</label>
                    <input type="text" id="notes" name="notes" 
                           value="<?php echo htmlspecialchars($_POST['notes'] ?? ''); ?>">
                </div>
                
                <div style="text-align: center; margin-top: 30px;">
                    <button type="submit" class="btn-primary">Create Maintenance Request</button>
                    <a href="maintman.php" class="btn-secondary" style="margin-left: 10px;">Cancel</a>
                </div>
            </form>
        </div>
        
      </div>

    </div>
  </main>
</body>
</html>
