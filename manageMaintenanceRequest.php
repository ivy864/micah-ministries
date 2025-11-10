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
    
    require_once('domain/Comment.php');
    require_once('database/dbComments.php');

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
            $result = add_comment($cmnt);
            
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
    
    // maintenance staff and above can access
    if ($accessLevel < 1) {
        header('Location: index.php');
        die();
    }

    // include database functions
    require_once('database/dbMaintenanceRequests.php');
    
    // get the request ID from URL
    $request_id = $_GET['id'] ?? null;
    
    if (!$request_id) {
        header('Location: viewAllMaintenanceRequests.php');
        die();
    }
    
    // get the maintenance request from database
    $request = get_maintenance_request_by_id($request_id);
    
    if (!$request) {
        header('Location: viewAllMaintenanceRequests.php');
        die();
    }
    
    $message = '';
    $error = '';
    $edit_mode = isset($_GET['edit']) && $_GET['edit'] == '1';
    
    // show success message if redirected from successful save
    if (isset($_GET['saved']) && $_GET['saved'] == '1') {
        $message = '✅ Changes saved successfully! The maintenance request has been updated.';
    }
    
    // helper function to render form fields
    function renderField($type, $name, $value, $edit_mode, $required = false, $options = []) {
        $base_style = "width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;";
        $readonly_style = "padding: 8px; display: inline-block; width: 100%; background: #f8f9fa; border: 1px solid #ddd; border-radius: 4px;";
        
        if ($edit_mode) {
            switch ($type) {
                case 'text':
                case 'email':
                case 'tel':
                    return '<input type="' . $type . '" name="' . $name . '" value="' . htmlspecialchars($value) . '" ' . 
                           ($required ? 'required ' : '') . 'style="' . $base_style . '">';
                case 'textarea':
                    $height = $options['height'] ?? '40px';
                    return '<textarea name="' . $name . '" ' . ($required ? 'required ' : '') . 
                           'style="' . $base_style . ' height: ' . $height . '; resize: vertical;">' . 
                           htmlspecialchars($value) . '</textarea>';
                case 'select':
                    $html = '<select name="' . $name . '" style="' . $base_style . '">';
                    foreach ($options['options'] as $option_value => $option_text) {
                        $selected = ($value == $option_value) ? ' selected' : '';
                        $html .= '<option value="' . htmlspecialchars($option_value) . '"' . $selected . '>' . 
                                htmlspecialchars($option_text) . '</option>';
                    }
                    $html .= '</select>';
                    return $html;
            }
        } else {
            // read-only mode
            $display_value = $value ?: ($options['placeholder'] ?? '');
            $class = '';
            if ($type == 'select' && isset($options['display_class'])) {
                $class = 'class="' . $options['display_class'] . '"';
            }
            return '<span ' . $class . ' style="' . $readonly_style . ' font-weight: bold;">' . 
                   htmlspecialchars($display_value) . '</span>';
        }
    }
    
    // handle form submission - only process if in edit mode
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && $edit_mode) {
        $requester_name = $_POST['requester_name'] ?? '';
        $requester_email = $_POST['requester_email'] ?? '';
        $requester_phone = $_POST['requester_phone'] ?? '';
        $location = $_POST['location'] ?? '';
        $building = $_POST['building'] ?? '';
        $unit = $_POST['unit'] ?? '';
        $description = $_POST['description'] ?? '';
        $priority = $_POST['priority'] ?? 'Medium';
        $status = $_POST['status'] ?? 'Pending';
        $assigned_to = $_POST['assigned_to'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        // basic validation
        if (empty($requester_name) || empty($description)) {
            $error = '❌ Please fill in all required fields. Requester name and description are required.';
        } else {
            // check if any changes were made
            $has_changes = false;
            if ($request->getRequesterName() !== $requester_name ||
                $request->getRequesterEmail() !== $requester_email ||
                $request->getRequesterPhone() !== $requester_phone ||
                $request->getLocation() !== $location ||
                $request->getBuilding() !== $building ||
                $request->getUnit() !== $unit ||
                $request->getDescription() !== $description ||
                $request->getPriority() !== $priority ||
                $request->getStatus() !== $status ||
                $request->getAssignedTo() !== $assigned_to ||
                $request->getNotes() !== $notes) {
                $has_changes = true;
            }
            
            if (!$has_changes) {
                $message = 'ℹ️ No changes were made. The form data is the same as before.';
            } else {
                // update the maintenance request object
                $request->setRequesterName($requester_name);
                $request->setRequesterEmail($requester_email);
                $request->setRequesterPhone($requester_phone);
                $request->setLocation($location);
                $request->setBuilding($building);
                $request->setUnit($unit);
                $request->setDescription($description);
                $request->setPriority($priority);
                $request->setStatus($status);
                $request->setAssignedTo($assigned_to);
                $request->setNotes($notes);
                
                // update in database using object
                $result = update_maintenance_request($request);
                
                if ($result) {
                    $message = '✅ Changes saved successfully! The maintenance request has been updated.';
                    // redirect to view mode after successful update
                    header('Location: ?id=' . urlencode($request_id) . '&saved=1');
                    exit;
                } else {
                    $error = '❌ Failed to save changes. Please check your connection and try again.';
                }
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>     <link rel="icon" type="image/png" href="images/micah-favicon.png">

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Micah Ministries | Manage Maintenance Request</title>
  <link href="css/base.css?v=<?php echo time(); ?>" rel="stylesheet">
  <script src="js/comment.js?v=<?php echo time(); ?>"></script>

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
	
	.password-help {
	    font-size: 12px;
	    color: #666;
	    margin-top: 5px;
	}
	
	/* priority and status styling */
	.priority-high {
	    color: #dc3545;
	    font-weight: bold;
	}
	
	.priority-medium {
	    color: #ffc107;
	    font-weight: bold;
	}
	
	.priority-low {
	    color: #28a745;
	}
	
	.priority-emergency {
	    color: #dc3545;
	    font-weight: bold;
	    background-color: #f8d7da;
	    padding: 2px 6px;
	    border-radius: 4px;
	}
	
	.status-pending {
	    color: #ffc107;
	    font-weight: bold;
	}
	
	.status-in-progress {
	    color: #17a2b8;
	    font-weight: bold;
	}
	
	.status-completed {
	    color: #28a745;
	    font-weight: bold;
	}
	
	.status-cancelled {
	    color: #6c757d;
	}
	
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
        <h1 class="main-text">Manage Maintenance Request</h1>
        <p class="secondary-text">
          View and edit maintenance request details. Use the form below to update request information.
        </p>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form id="maintenance-form" method="POST" action="">
                <div class="form-group">
                    <label>Request ID</label>
                    <input type="text" value="<?php echo htmlspecialchars($request->getID()); ?>" readonly style="background-color: #f8f9fa;">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="requester_name">Requester Name *</label>
                        <?php echo renderField('text', 'requester_name', $request->getRequesterName(), $edit_mode, true); ?>
                    </div>
                    <div class="form-group">
                        <label for="requester_email">Email</label>
                        <?php echo renderField('email', 'requester_email', $request->getRequesterEmail(), $edit_mode); ?>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="requester_phone">Phone</label>
                        <?php echo renderField('tel', 'requester_phone', $request->getRequesterPhone(), $edit_mode); ?>
                    </div>
                    <div class="form-group">
                        <label for="priority">Priority</label>
                        <?php echo renderField('select', 'priority', $request->getPriority(), $edit_mode, false, [
                            'options' => ['Low' => 'Low', 'Medium' => 'Medium', 'High' => 'High', 'Emergency' => 'Emergency'],
                            'display_class' => 'priority-' . strtolower($request->getPriority())
                        ]); ?>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="location">Location</label>
                        <?php echo renderField('text', 'location', $request->getLocation(), $edit_mode); ?>
                    </div>
                    <div class="form-group">
                        <label for="building">Building</label>
                        <?php echo renderField('text', 'building', $request->getBuilding(), $edit_mode); ?>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="unit">Unit</label>
                        <?php echo renderField('text', 'unit', $request->getUnit(), $edit_mode); ?>
                    </div>
                    <div class="form-group">
                        <label for="assigned_to">Assigned To</label>
                        <?php echo renderField('text', 'assigned_to', $request->getAssignedTo(), $edit_mode, false, ['placeholder' => 'Unassigned']); ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Description *</label>
                    <?php echo renderField('textarea', 'description', $request->getDescription(), $edit_mode, true, ['height' => '40px']); ?>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <?php echo renderField('select', 'status', $request->getStatus(), $edit_mode, false, [
                            'options' => ['Pending' => 'Pending', 'In Progress' => 'In Progress', 'Completed' => 'Completed', 'Cancelled' => 'Cancelled'],
                            'display_class' => 'status-' . strtolower(str_replace(' ', '-', $request->getStatus()))
                        ]); ?>
                    </div>
                    <div class="form-group">
                        <label>Created</label>
                        <input type="text" value="<?php echo date('M j, Y g:i A', strtotime($request->getCreatedAt())); ?>" readonly style="background-color: #f8f9fa;">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="notes">Notes</label>
                    <?php echo renderField('textarea', 'notes', $request->getNotes(), $edit_mode, false, ['height' => '40px', 'placeholder' => 'No notes']); ?>
                </div>
                
                <div style="text-align: center; margin-top: 30px;">
                    <?php if (!$edit_mode): ?>
                        <a href="?id=<?php echo urlencode($request_id); ?>&edit=1" class="btn-primary">Edit</a>
                    <?php else: ?>
                        <a href="#" onclick="document.getElementById('maintenance-form').submit(); return false;" class="btn-primary">Save</a>
                        <a href="?id=<?php echo urlencode($request_id); ?>" class="btn-secondary" style="margin-left: 10px;">Cancel</a>
                    <?php endif; ?>
                    <a href="viewAllMaintenanceRequests.php" class="btn-secondary" style="margin-left: 10px;">Back to List</a>
                </div>
            </form>
        </div>
        
        <div id="comments" requestID="<?php echo htmlspecialchars($request->getID()) ?>">
            <?php
            $comments = get_comments($_GET['id']);
            ?>
            <h2>Comments</h2>
            <script >
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

