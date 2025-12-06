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
    // maintenance staff and above can access
    if ($accessLevel < 1) {
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
        } 
        else {
            // create maintenance request object
            $maintenanceRequest = new MaintenanceRequest(
                $id, $requester_name, $requester_email, $requester_phone, 
                $location, $building, $unit, $description, $priority, 
                'Pending', $assigned_to, $notes
            );
            
            // add to database using object
            $result = add_maintenance_request($maintenanceRequest);
            
            if ($result) {

                /* ============================================================
                   CHANGE: MULTIPLE IMAGE UPLOAD WITH SIZE/TYPE VALIDATION
                   ============================================================ */

                require_once('database/dbMaintenanceImages.php');
                $max_size = 900 * 1024; // 900 KB limit PER IMAGE

                if (isset($_FILES['attachments']) &&
                    is_array($_FILES['attachments']['tmp_name'])) {

                    $count = count($_FILES['attachments']['tmp_name']);

                    for ($i = 0; $i < $count; $i++) {

                        // Skip empty inputs
                        if (!is_uploaded_file($_FILES['attachments']['tmp_name'][$i])) {
                            continue;
                        }

                        $file_name = $_FILES['attachments']['name'][$i];
                        $file_type = $_FILES['attachments']['type'][$i];
                        $file_size = $_FILES['attachments']['size'][$i];
                        $file_error = $_FILES['attachments']['error'][$i];

                        // upload error
                        if ($file_error !== UPLOAD_ERR_OK) {
                            $error = "Upload error on file '{$file_name}' (error code {$file_error}).";
                            break;
                        }

                        // too big
                        if ($file_size > $max_size) {
                            $size_kb = round($file_size / 1024);
                            $error = "❌ File '{$file_name}' is too large ({$size_kb} KB). Maximum allowed is 900 KB.";
                            break;
                        }

                        // only PNG
                        if ($file_type !== "image/png") {
                            $error = "❌ File '{$file_name}' must be a PNG image.";
                            break;
                        }

                        // safe to read blob
                        $file_blob = file_get_contents($_FILES['attachments']['tmp_name'][$i]);
                        if ($file_blob === false) {
                            $error = "❌ Failed to read file '{$file_name}'.";
                            break;
                        }

                        // save to DB
                        $save_result = add_maintenance_image($id, $file_name, $file_type, $file_blob);
                        if (!$save_result) {
                            $error = "❌ Failed to save image '{$file_name}'.";
                            break;
                        }
                    }

                    // if upload failed, do NOT clear form; display error
                    if (!empty($error)) {
                        // stop before success block
                    }
                    else {
                        $message = "Maintenance request created successfully! Request ID: $id (images uploaded)";
                        $_POST = [];
                    }

                } 
                else {
                    // no attachments
                    $message = 'Maintenance request created successfully! Request ID: ' . $id;
                    $_POST = [];
                }

            } else {
                $error = 'Failed to create maintenance request. Please try again.';
            }

        } // end else
    } // end POST
?>
<!DOCTYPE html>
<html lang="en">
<head>     <link rel="icon" type="image/png" href="images/micah-favicon.png">

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Maintenance Request</title>
  <link href="css/base.css?v=<?php echo time(); ?>" rel="stylesheet">

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
        <h1 class="main-text">Create Maintenance Request</h1>
        <div class="div-blue"></div>
        <p class="secondary-text">
          Submit a new maintenance request. Fill out the form below with all required information.
        </p>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="form-container">
            <form method="POST" enctype="multipart/form-data" action="">
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
                
                <div class="form-group">
                    <label for="attachments">Upload Images (PNG only)</label>
                    <!-- CHANGE: allow multiple images -->
                    <input type="file" id="attachments" name="attachments[]" accept="image/png" multiple>
                </div>

                <div style="text-align: center; margin-top: 30px;">
                    <button type="submit" class="blue-button">Create Maintenance Request</button>
                    <a href="maintman.php" class="gray-button" style="margin-left: 10px;">Cancel</a>
                </div>
            </form>
        </div>
        
      </div>

    </div>
  </main>
</body>
</html>
