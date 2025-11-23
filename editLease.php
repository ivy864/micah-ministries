<?php
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

require_once('domain/Comment.php');
require_once('database/dbComments.php');

if ($accessLevel < 2) {
    header('Location: index.php');
    die();
}
$lease_id = $_GET['id'] ?? null;

// if writecomment is set to true in request header, write a comment to database
    if (isset($_SERVER['HTTP_WRITECOMMENT']) && $_SERVER['HTTP_WRITECOMMENT'] == 'True') {
        // Start output buffering to capture any unwanted output
        ob_start();
        
        // Suppress any PHP errors/warnings that might output HTML
        error_reporting(0);
        ini_set('display_errors', 0);
        
        // Clear any previous output
        ob_clean();
        
        // Set proper headers for JSON response
        header('Content-Type: application/json');
        
        // Debug: Log what we received
        error_log("Comment submission received. UserID: " . $userID . ", RequestID: " . $_GET['id'] . ", Comment: " . $_POST['comment']);
        
        try {
            $cmnt = new Comment($userID, $_GET['id'], $_POST['comment'], time());
            $result = add_lease_comment($cmnt);
            
            if ($result) {
                // Debug: Log the result
                error_log("Comment add result: success");
                
                // Get the JSON response
                $jsonResponse = $cmnt->toJSON();
                error_log("JSON response: " . $jsonResponse);
                
                // Clear any output buffer and send clean JSON
                ob_clean();
                echo $jsonResponse;
            } else {
                // Debug: Log the result
                error_log("Comment add result: failed");
                
                // Return error response
                ob_clean();
                http_response_code(500);
                echo json_encode(['error' => 'Failed to save comment']);
            }
        } catch (Exception $e) {
            error_log("Comment submission error: " . $e->getMessage());
            ob_clean();
            http_response_code(500);
            echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
        }
        
        // End output buffering and don't render the rest of the page
        ob_end_flush();
        exit();
    }
    if (isset($_SERVER['HTTP_DELETECOMMENT']) && $_SERVER['HTTP_DELETECOMMENT'] == 'True') {
        $cmnt = new Comment($userID, $_GET['id'], '', $_POST['time']);
        delete_comment($cmnt);
        exit();
    }
    if (isset($_SERVER['HTTP_GETCOMMENTS']) && $_SERVER['HTTP_GETCOMMENTS'] == 'True') {
        exit();
    }

$pdo = null;
$db_notice = null;
$lease = [
    'tenant_first_name' => '',
    'tenant_last_name' => '',
    'property_street' => '',
    'unit_number' => '',
    'property_city' => '',
    'property_state' => '',
    'property_zip' => '',
    'start_date' => '',
    'expiration_date' => '',
    'monthly_rent' => '',
    'security_deposit' => '',
    'lease_form' => '',
    'case_manager' => '',
    'program_type' => '',
    'status' => 'Active'
];

try {
    $conf = null;
    $confPath = __DIR__ . '/database/dbViewLease.php';

    if (file_exists($confPath)) {
        include $confPath; // sets $conf
    }

    $dsn  = $conf['dsn']  ?? getenv('MICAH_DB_DSN');
    $user = $conf['user'] ?? getenv('MICAH_DB_USER');
    $pass = $conf['pass'] ?? getenv('MICAH_DB_PASS');

    if ($dsn) {
        $pdo = new PDO($dsn, $user ?: null, $pass ?: null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } else {
        $db_notice = "Database not yet configured — please fill in database/dbViewLease.php."; // change to actual db
    }
} catch (Throwable $e) {
    $db_notice = "Database connection failed: " . htmlspecialchars($e->getMessage());
}



if ($pdo && $lease_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT tenant_first_name, tenant_last_name, property_street, unit_number, 
                property_city, property_state, property_zip, start_date, 
                expiration_date, monthly_rent, security_deposit, 
                LENGTH(lease_form) as lease_form_size, case_manager, program_type, status 
            FROM dbleases 
            WHERE id = :id 
            LIMIT 1
        ");
        $stmt->execute([':id' => $lease_id]);
        if ($row = $stmt->fetch()) {
            $lease = array_merge($lease, $row);
        } else {
            $db_notice = "No lease found for ID " . htmlspecialchars($lease_id);
        }
    } catch (Throwable $e) {
        $db_notice = "Error loading lease: " . htmlspecialchars($e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $lease_id) {
    require_once('database/dbLeases.php');
    require_once('domain/Lease.php');
    
    // Get the existing lease
    $existing_lease = get_lease_by_id($lease_id);
    
    if ($existing_lease) {
        // Update with new values from form
        if (isset($_POST['tenant_first_name'])) $existing_lease->setTenantFirstName($_POST['tenant_first_name']);
        if (isset($_POST['tenant_last_name'])) $existing_lease->setTenantLastName($_POST['tenant_last_name']);
        if (isset($_POST['property_street'])) $existing_lease->setPropertyStreet($_POST['property_street']);
        if (isset($_POST['unit_number'])) $existing_lease->setUnitNumber($_POST['unit_number']);
        if (isset($_POST['property_city'])) $existing_lease->setPropertyCity($_POST['property_city']);
        if (isset($_POST['property_state'])) $existing_lease->setPropertyState($_POST['property_state']);
        if (isset($_POST['property_zip'])) $existing_lease->setPropertyZip($_POST['property_zip']);
        if (isset($_POST['start_date'])) $existing_lease->setStartDate($_POST['start_date']);
        if (isset($_POST['expiration_date'])) $existing_lease->setExpirationDate($_POST['expiration_date']);
        if (isset($_POST['monthly_rent'])) $existing_lease->setMonthlyRent($_POST['monthly_rent']);
        if (isset($_POST['security_deposit'])) $existing_lease->setSecurityDeposit($_POST['security_deposit']);
        if (isset($_POST['case_manager'])) $existing_lease->setCaseManager($_POST['case_manager']);
        if (isset($_POST['program_type'])) $existing_lease->setProgramType($_POST['program_type']);
        if (isset($_POST['status'])) $existing_lease->setStatus($_POST['status']);
        
        // Handle file upload
        if (isset($_FILES['lease_form']) && $_FILES['lease_form']['error'] == UPLOAD_ERR_OK) {
            $lease_form = file_get_contents($_FILES['lease_form']['tmp_name']);
            $existing_lease->setLeaseForm($lease_form);
            error_log("New PDF uploaded for lease " . $lease_id . ". Size: " . strlen($lease_form) . " bytes");
        }
        
        // Update in database
        $result = update_lease($existing_lease);
        
        if ($result) {
            $db_notice = "✅ Lease updated successfully.";
            // Reload the lease to show updated data
            $lease = get_lease_by_id($lease_id);
            if ($lease) {
                // Convert Lease object to array for display
                $lease_array = [
                    'tenant_first_name' => $lease->getTenantFirstName(),
                    'tenant_last_name' => $lease->getTenantLastName(),
                    'property_street' => $lease->getPropertyStreet(),
                    'unit_number' => $lease->getUnitNumber(),
                    'property_city' => $lease->getPropertyCity(),
                    'property_state' => $lease->getPropertyState(),
                    'property_zip' => $lease->getPropertyZip(),
                    'start_date' => $lease->getStartDate(),
                    'expiration_date' => $lease->getExpirationDate(),
                    'monthly_rent' => $lease->getMonthlyRent(),
                    'security_deposit' => $lease->getSecurityDeposit(),
                    'program_type' => $lease->getProgramType(),
                    'status' => $lease->getStatus(),
                    'lease_form_size' => $lease->getLeaseForm() ? strlen($lease->getLeaseForm()) : 0,
                    'case_manager' => $lease->getCaseManager()
                ];
                $lease = $lease_array;
            }
        } else {
            $db_notice = "❌ Update failed.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>     <link rel="icon" type="image/png" href="images/micah-favicon.png">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Micah Ministries | Edit Lease</title>
    <link href="css/base.css?v=<?php echo time(); ?>" rel="stylesheet">
    <script src="js/comment.js?v=<?php echo time(); ?>"></script>
    <?php
    $tailwind_mode = true;
    require_once('header.php');
    ?>

    <style>
        /* Comments section styling */
	#comments {
	    margin-top: 30px;
	    padding: 20px;
	    background: #f8f9fa;
	    border-radius: 8px;
	    border: 1px solid #e9ecef;
	}
	
	#comments h2 {
	    margin-bottom: 20px;
	    color: #274471;
	    font-size: 18px;
	    border-bottom: 2px solid #274471;
	    padding-bottom: 10px;
	}
	
	#comment-container {
	    margin-bottom: 20px;
	}
	
	#comment-container > div {
	    background: white;
	    border: 1px solid #dee2e6;
	    border-radius: 6px;
	    margin-bottom: 15px;
	    padding: 15px;
	    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
	}
	
	.comment-head {
	    display: flex;
	    justify-content: space-between;
	    align-items: center;
	    margin-bottom: 8px;
	}
	
	.comment-title {
	    font-weight: bold;
	    color: #274471;
	    font-size: 14px;
	}
	
	.comment-timestamp {
	    font-size: 12px;
	    color: #6c757d;
	}
	
	.comment-content {
	    color: #495057;
	    line-height: 1.4;
	    margin-top: 8px;
	}
	
	#commentBox {
	    width: 100%;
	    padding: 12px;
	    border: 1px solid #ced4da;
	    border-radius: 4px;
	    font-size: 14px;
	    resize: vertical;
	    min-height: 80px;
	    margin-bottom: 15px;
	}
	
	#commentBox:focus {
	    outline: none;
	    border-color: #274471;
	    box-shadow: 0 0 0 2px rgba(39, 68, 113, 0.25);
	}
	
	/* fix comment button styling */
	#comments button {
	    background-color: #274471;
	    color: white !important;
	    border: none;
	    padding: 10px 20px;
	    border-radius: 4px;
	    cursor: pointer;
	    font-size: 14px;
	    font-weight: 500;
	}
	
	#comments button:hover {
	    background-color: #1e3554;
	}
    </style>
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
        <h1 class="main-text">Edit Lease</h1>
        <div class="div-blue"></div>
        <p class="secondary-text">
          Update existing lease information. Modify the fields below as needed and submit your changes to save the updated lease details.
        </p>
        
        <?php if (isset($db_notice) && $db_notice): ?>
            <div class="alert <?php echo ($pdo ? 'alert-success' : 'alert-error'); ?>">
                <?php echo $db_notice; ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="tenant_first_name">Tenant First Name:</label>
                        <input type="text" id="tenant_first_name" name="tenant_first_name"
                            value="<?php echo htmlspecialchars($lease['tenant_first_name']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="tenant_last_name">Tenant Last Name:</label>
                        <input type="text" id="tenant_last_name" name="tenant_last_name"
                            value="<?php echo htmlspecialchars($lease['tenant_last_name']); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="property_street">Property Street:</label>
                    <input type="text" id="property_street" name="property_street"
                        value="<?php echo htmlspecialchars($lease['property_street']); ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="property_city">Property City:</label>
                        <input type="text" id="property_city" name="property_city"
                            value="<?php echo htmlspecialchars($lease['property_city']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="property_state">Property State:</label>
                        <input type="text" id="property_state" name="property_state"
                            value="<?php echo htmlspecialchars($lease['property_state']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="property_zip">Property ZIP:</label>
                        <input type="text" id="property_zip" name="property_zip"
                            value="<?php echo htmlspecialchars($lease['property_zip']); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="unit_number">Unit Number:</label>
                        <input type="text" id="unit_number" name="unit_number"
                            value="<?php echo htmlspecialchars($lease['unit_number']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="program_type">Program Type:</label>
                        <select id="program_type" name="program_type">
                            <option value="">Select Program Type</option>
                            <option value="Program #1" <?php echo ($lease['program_type'] == 'Program #1') ? 'selected' : ''; ?>>Program #1</option>
                            <option value="Program #2" <?php echo ($lease['program_type'] == 'Program #2') ? 'selected' : ''; ?>>Program #2</option>
                            <option value="Emergency Housing" <?php echo ($lease['program_type'] == 'Emergency Housing') ? 'selected' : ''; ?>>Emergency Housing</option>
                            <option value="Transitional Housing" <?php echo ($lease['program_type'] == 'Transitional Housing') ? 'selected' : ''; ?>>Transitional Housing</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status">Lease Status:</label>
                        <select id="status" name="status">
                            <option value="Active" <?php echo ($lease['status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                            <option value="Expired" <?php echo ($lease['status'] == 'Expired') ? 'selected' : ''; ?>>Expired</option>
                            <option value="Terminated" <?php echo ($lease['status'] == 'Terminated') ? 'selected' : ''; ?>>Terminated</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="start_date">Lease Start Date:</label>
                        <input type="date" id="start_date" name="start_date"
                            value="<?php echo htmlspecialchars($lease['start_date']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="expiration_date">Lease End Date:</label>
                        <input type="date" id="expiration_date" name="expiration_date"
                            value="<?php echo htmlspecialchars($lease['expiration_date']); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="monthly_rent">Rent Amount:</label>
                        <input type="number" step="0.01" id="monthly_rent" name="monthly_rent"
                            value="<?php echo htmlspecialchars($lease['monthly_rent']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="security_deposit">Security Deposit:</label>
                        <input type="number" step="0.01" id="security_deposit" name="security_deposit"
                            value="<?php echo htmlspecialchars($lease['security_deposit']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="case_manager">Case Manager Name:</label>
                        <input type="text" id="case_manager" name="case_manager"
                            value="<?php echo htmlspecialchars($lease['case_manager']); ?>">
                    </div>
                    </div>

                    <div class="form-group">
                        <label for="lease_form">Lease Form:</label>
                        <input type="file" id="lease_form" name="lease_form" accept="application/pdf">
                        
                        <?php if (isset($lease['lease_form_size']) && $lease['lease_form_size'] > 0): ?>
                            <div style="margin-top: 15px; display: flex; justify-content: center;">
                                <iframe 
                                    src="viewLeasePDF.php?id=<?php echo urlencode($lease_id); ?>" 
                                    width="100%" 
                                    height="600px" 
                                    style="border: 1px solid #dee2e6; border-radius: 4px;">
                                </iframe>
                            </div>
                        <?php else: ?>
                            <p style="font-size: 12px; color: #6c757d; margin-top: 5px;">
                                No lease document currently uploaded
                            </p>
                        <?php endif; ?>
                    </div>

                <div style="margin-top: 30px; text-align: center;">
                    <button type="submit" class="blue-button">Submit Changes</button>
                </div>
                
                <div style="margin-top: 20px; text-align: center;">
                    <a href="index.php" class="gray-button">Return to Dashboard</a>
                </div>
            </form>
        </div>

        <div id="comments" requestID="<?php echo htmlspecialchars($lease_id) ?>">
            <?php
            $comments = get_lease_comments($lease_id);
            ?>
            <h2>Comments</h2>
            <script>
                /*
                * comments are rendered client-side with js.
                * The array of comments is encoded in json so it can be used by the js/comment.js file
                */
                let comments = <?php echo json_encode($comments) ?>;
            </script>
            <div id="comment-container">
                
            </div>
            <textarea id="commentBox"></textarea>
            <button onclick='writeComment()'>Comment</button>
        </div>
      </div>
    </div>
  </main>
</body>
</html>