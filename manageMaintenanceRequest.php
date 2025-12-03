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
            
            // If an image was uploaded, that counts as a change
            if (!empty($_FILES['attachment']['tmp_name'])) {
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

// Handle image upload
if (!empty($_FILES['attachment']['tmp_name'])) {

    require_once('database/dbMaintenanceImages.php');

    $file_name = $_FILES['attachment']['name'];
    $file_type = $_FILES['attachment']['type'];
    $file_blob = file_get_contents($_FILES['attachment']['tmp_name']);

    if ($file_type === "image/png") {
        add_maintenance_image($request_id, $file_name, $file_type, $file_blob);
    } else {
        $error = "❌ Only PNG images are allowed.";
    }
}

if ($result) {
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
            <form id="maintenance-form" method="POST" enctype="multipart/form-data" action="">
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
                        <a href="?id=<?php echo urlencode($request_id); ?>&edit=1" class="blue-button">Edit</a>
                    <?php else: ?>
                        <?php if ($edit_mode): ?>
                    <div class="form-group">
                        <label for="attachment">Upload Image (PNG only)</label>
                        <input type="file" id="attachment" name="attachment" accept="image/png">
                    </div>
                    <?php endif; ?>

                        <a href="#" onclick="document.getElementById('maintenance-form').submit(); return false;" class="btn-primary">Save</a>
                        <a href="?id=<?php echo urlencode($request_id); ?>" class="btn-secondary" style="margin-left: 10px;">Cancel</a>
                    <?php endif; ?>
                    <a href="viewAllMaintenanceRequests.php" class="gray-button" style="margin-left: 10px;">Back to List</a>
                </div>
            </form>
        </div>
        
        <?php
require_once('database/dbMaintenanceImages.php');
$images = get_maintenance_images_by_request($request->getID());
?>

<div class="attachments-section" style="margin-top: 30px;">
    <h2 style="color:var(--main-color);">Attachments</h2>

    <?php if (empty($images)): ?>
        <p style="color:#6c757d;">No images uploaded.</p>
    <?php else: ?>
        <div style="display:flex;flex-wrap:wrap;gap:12px;">
            <?php foreach ($images as $img): ?>
                <a href="viewMaintenanceImage.php?id=<?php echo $img->getID(); ?>" target="_blank">
                    <img src="viewMaintenanceImage.php?id=<?php echo $img->getID(); ?>" 
                         style="width:150px; border-radius:6px; border:1px solid #ccc;">
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>


        <div id="comments" requestID="<?php echo htmlspecialchars($request->getID()) ?>">
            <?php
            $comments = get_comments($_GET['id']);
            ?>
            <h2 style="color:var(--main-color);">Comments</h2>
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

