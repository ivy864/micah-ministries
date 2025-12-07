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
require_once('database/dbPersons.php');

if ($accessLevel < 2) {
    header('Location: index.php');
    die();
}
$lease_id = $_GET['id'] ?? null;

$caseManagersList = getCaseManagers();

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

require_once('database/dbLeases.php');
require_once('domain/Lease.php');

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

// Load existing lease data if editing
if ($lease_id) {
    $existing_lease = get_lease_by_id($lease_id);
    
    if ($existing_lease) {
        // Convert Lease object to array for display
        $lease = [
            'tenant_first_name' => $existing_lease->getTenantFirstName(),
            'tenant_last_name' => $existing_lease->getTenantLastName(),
            'property_street' => $existing_lease->getPropertyStreet(),
            'unit_number' => $existing_lease->getUnitNumber(),
            'property_city' => $existing_lease->getPropertyCity(),
            'property_state' => $existing_lease->getPropertyState(),
            'property_zip' => $existing_lease->getPropertyZip(),
            'start_date' => $existing_lease->getStartDate(),
            'expiration_date' => $existing_lease->getExpirationDate(),
            'monthly_rent' => $existing_lease->getMonthlyRent(),
            'security_deposit' => $existing_lease->getSecurityDeposit(),
            'case_manager' => $existing_lease->getCaseManager(),
            'program_type' => $existing_lease->getProgramType(),
            'status' => $existing_lease->getStatus(),
            'lease_form_size' => $existing_lease->getLeaseForm() ? strlen($existing_lease->getLeaseForm()) : 0
        ];
        error_log("Loaded case_manager from DB: " . ($lease['case_manager'] ?? 'NULL'));
    } else {
        $db_notice = "No lease found for ID " . htmlspecialchars($lease_id);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $lease_id) {

    error_log("POST data received - case_manager: " . ($_POST['case_manager'] ?? 'NOT SET'));

    require_once('database/dbLeases.php');
    require_once('domain/Lease.php');
    require_once('database/dbPersons.php');
    
    // Get the existing lease WITH ITS BLOB
    $existing_lease = get_lease_by_id($lease_id);
    $caseManagersList = getCaseManagers();
    
    if ($existing_lease) {
        // Debug: Check blob before updates
        $blob_before = $existing_lease->getLeaseForm();
        error_log("BEFORE updates - Blob size: " . ($blob_before ? strlen($blob_before) : 0));
        
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
        
        // Handle file upload - ONLY update if a new file was uploaded
        if (isset($_FILES['lease_form']) && $_FILES['lease_form']['error'] === UPLOAD_ERR_OK) {
            $lease_form = file_get_contents($_FILES['lease_form']['tmp_name']);
            $existing_lease->setLeaseForm($lease_form);
            error_log("New PDF uploaded: " . strlen($lease_form) . " bytes");
        } else {
            // Debug: Log file upload status
            $error_code = $_FILES['lease_form']['error'] ?? 'not set';
            error_log("No new file uploaded. Error code: " . $error_code . " (4 = UPLOAD_ERR_NO_FILE)");
        }
        
        // Debug: Check blob before database update
        $blob_after = $existing_lease->getLeaseForm();
        error_log("BEFORE database update - Blob size: " . ($blob_after ? strlen($blob_after) : 0));
        
        // Update in database
        $result = update_lease($existing_lease);
        
        if ($result) {
            $db_notice = "Lease updated successfully.";
            
            // Debug: Verify what was saved
            $verify_lease = get_lease_by_id($lease_id);
            $verify_blob = $verify_lease ? $verify_lease->getLeaseForm() : null;
            error_log("AFTER database update - Retrieved blob size: " . ($verify_blob ? strlen($verify_blob) : 0));
            
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
            $db_notice = "Update failed.";
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
                        <select id="property_state" name="property_state" required>
                            <?php 
                            $states = [
                                'AL'=>'Alabama','AK'=>'Alaska','AZ'=>'Arizona','AR'=>'Arkansas','CA'=>'California',
                                'CO'=>'Colorado','CT'=>'Connecticut','DE'=>'Delaware','DC'=>'District Of Columbia',
                                'FL'=>'Florida','GA'=>'Georgia','HI'=>'Hawaii','ID'=>'Idaho','IL'=>'Illinois','IN'=>'Indiana',
                                'IA'=>'Iowa','KS'=>'Kansas','KY'=>'Kentucky','LA'=>'Louisiana','ME'=>'Maine','MD'=>'Maryland',
                                'MA'=>'Massachusetts','MI'=>'Michigan','MN'=>'Minnesota','MS'=>'Mississippi','MO'=>'Missouri',
                                'MT'=>'Montana','NE'=>'Nebraska','NV'=>'Nevada','NH'=>'New Hampshire','NJ'=>'New Jersey',
                                'NM'=>'New Mexico','NY'=>'New York','NC'=>'North Carolina','ND'=>'North Dakota','OH'=>'Ohio',
                                'OK'=>'Oklahoma','OR'=>'Oregon','PA'=>'Pennsylvania','RI'=>'Rhode Island','SC'=>'South Carolina',
                                'SD'=>'South Dakota','TN'=>'Tennessee','TX'=>'Texas','UT'=>'Utah','VT'=>'Vermont','VA'=>'Virginia',
                                'WA'=>'Washington','WV'=>'West Virginia','WI'=>'Wisconsin','WY'=>'Wyoming'
                            ];

                            // Detect selected state (from POST or existing lease)
                            $selectedState = $_POST['property_state'] ?? ($lease['property_state'] ?? '');
                            ?>

                            <?php foreach ($states as $abbr => $name): ?>
                                <option value="<?php echo $abbr; ?>" 
                                    <?php echo ($selectedState === $abbr) ? 'selected' : ''; ?>>
                                    <?php echo $name; ?>
                                </option>
                            <?php endforeach; ?>
                    </select>
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
                        <label for="program_type">Funding Source:</label>
                        <select id="program_type" name="program_type">
                            <option value="">Select Funding Source</option>
                            <option value="HTF - PSH" <?php echo ($lease['program_type'] == 'HTF - PSH') ? 'selected' : ''; ?>>HTF - PSH</option>
                            <option value="HTF - RRH" <?php echo ($lease['program_type'] == 'HTF - RRH') ? 'selected' : ''; ?>>HTF - RRH</option>
                            <option value="FISH (HUD) - PSH" <?php echo ($lease['program_type'] == 'FISH (HUD) - PSH') ? 'selected' : ''; ?>>FISH (HUD) - PSH</option>
                            <option value="VHSP - RRH" <?php echo ($lease['program_type'] == 'VHSP - RRH') ? 'selected' : ''; ?>>VHSP - RRH</option>
                            <option value="Unsheltered - RRH" <?php echo ($lease['program_type'] == 'Unsheltered - RRH') ? 'selected' : ''; ?>>Unsheltered - RRH</option>
                            <option value="Journey Sustainable" <?php echo ($lease['program_type'] == 'Journey Sustainable') ? 'selected' : ''; ?>>Journey Sustainable</option>
                            <option value="HOME ARP - TBRA" <?php echo ($lease['program_type'] == 'HOME ARP - TBRA') ? 'selected' : ''; ?>>HOME ARP - TBRA</option>
                            <option value="Other" <?php echo ($lease['program_type'] == 'Other') ? 'selected' : ''; ?>>Other</option>
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
                        <label for="case_manager">Case Manager Name <span class="required">*</span></label>
                        <select name="case_manager" id="case_manager" required>
                            <?php
                            if ($caseManagersList && $caseManagersList->num_rows > 0) {
                                // Get the current case manager value
                                $currentCaseManager = $lease['case_manager'] ?? '';
                                
                                // Debug - remove this later
                                error_log("Current case manager for dropdown: '" . $currentCaseManager . "'");
                                
                                // Reset the result pointer to the beginning
                                mysqli_data_seek($caseManagersList, 0);
                                
                                while ($row = $caseManagersList->fetch_assoc()) {
                                    $fullName = $row['first_name'] . ' ' . $row['last_name'];
                                    // Compare the full name to the current case manager
                                    $selected = (trim($currentCaseManager) == trim($fullName)) ? 'selected' : '';
                                    
                                    // Debug - remove this later
                                    error_log("Comparing '" . trim($currentCaseManager) . "' with '" . trim($fullName) . "' - selected: " . ($selected ? 'YES' : 'NO'));
                                    
                                    echo "<option value='" . htmlspecialchars($fullName) . "' " . $selected . ">"
                                        . htmlspecialchars($fullName)
                                        . "</option>";
                                }
                            } else {
                                echo "<option disabled>No case managers found</option>";
                            }
                            ?>
                        </select>
                    </div>
                    </div>

                    <div class="form-group">
                        <label for="lease_form">Lease Form (PDF):</label>
                        
                        <?php if (isset($lease['lease_form_size']) && $lease['lease_form_size'] > 0): ?>
                            <div style="margin-bottom: 15px; padding: 12px; background: #e7f3ff; border: 1px solid #b3d9ff; border-radius: 4px;">
                                <strong style="color: #0066cc;">Current PDF:</strong> 
                                <span style="color: #333;">Lease document uploaded (<?php echo number_format($lease['lease_form_size']); ?> bytes)</span>
                                <br>
                                <small style="color: #666;">Upload a new file below only if you want to replace it</small>
                            </div>
                            
                            <div style="margin-top: 15px; margin-bottom: 15px; display: flex; justify-content: center;">
                                <iframe 
                                    src="viewLeasePDF.php?id=<?php echo urlencode($lease_id); ?>" 
                                    width="100%" 
                                    height="600px" 
                                    style="border: 1px solid #dee2e6; border-radius: 4px;">
                                </iframe>
                            </div>
                        <?php else: ?>
                            <p style="font-size: 14px; color: #856404; background: #fff3cd; padding: 10px; border-radius: 4px; border: 1px solid #ffeaa7;">
                                No lease document currently uploaded
                            </p>
                        <?php endif; ?>
                        
                        <div style="margin-top: 10px;">
                            <input type="file" id="lease_form" name="lease_form" accept="application/pdf">
                            <small style="display: block; margin-top: 5px; color: #6c757d;">
                                <?php if (isset($lease['lease_form_size']) && $lease['lease_form_size'] > 0): ?>
                                    Leave blank to keep existing PDF, or select a new file to replace it
                                <?php else: ?>
                                    Upload a PDF lease document
                                <?php endif; ?>
                            </small>
                        </div>
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