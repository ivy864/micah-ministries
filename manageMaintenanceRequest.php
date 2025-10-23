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
    
    // handle form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
            $error = 'Requester name and description are required.';
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
                $message = 'Maintenance request updated successfully!';
            } else {
                $error = 'Failed to update maintenance request. Please try again.';
            }
        }
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <?php require_once('universal.inc') ?>
        <title>Micah Ministries | Manage Maintenance Request</title>
        <link href="css/normal_tw.css" rel="stylesheet">

        <style>
            .manage-maintenance-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin: .5rem;
            }
            .manage-maintenance-header > button {
                width: initial;
            }
            td {
                padding: .3rem;
                border-bottom: 1px solid #eee;
                font-size: 14px;
            }
            
            /* make table wider and more compact */
            .main-content-box {
                max-width: 90% !important;
                width: 100% !important;
            }
            
            table {
                width: 100%;
                font-size: 14px;
            }
            
            input, select, textarea {
                font-size: 13px !important;
                padding: 6px !important;
            }
            
            textarea {
                height: 50px !important;
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
            
            /* fix header z-index to stay on top */
            header {
                position: fixed !important;
                top: 0 !important;
                width: 100% !important;
                z-index: 1000 !important;
            }
            
            /* add margin to main content to account for fixed header */
            main {
                margin-top: 80px !important;
            }
            
            /* improve button layout */
            .form-buttons {
                display: flex;
                gap: 15px;
                justify-content: center;
                margin-top: 20px;
            }
            
            .form-buttons button,
            .form-buttons a {
                padding: 12px 24px !important;
                font-size: 16px !important;
                min-width: 140px !important;
                text-align: center;
            }
        </style>
    </head>
    <body>
    
        <?php require_once('header.php') ?>
        <main>
            <div class="main-content-box  p-8 w-full max-w-3xl">
                <div class="manage-maintenance-header">
                    <h2>Edit Maintenance Request</h2>
                    <a href="viewAllMaintenanceRequests.php" class="btn-secondary" style="display: inline-block; padding: 8px 16px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px;">Back to List</a>
                </div>
                
                <?php if ($message): ?>
                    <div class="alert alert-success" style="padding: 15px; margin-bottom: 20px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px;">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-error" style="padding: 15px; margin-bottom: 20px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px;">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <table>
                    <tr>
                        <td><strong>Request ID:</strong></td>
                        <td><?php echo htmlspecialchars($request->getID()); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Requester Name *:</strong></td>
                        <td><input type="text" name="requester_name" value="<?php echo htmlspecialchars($request->getRequesterName()); ?>" required style="width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px; font-size: 13px;"></td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td><input type="email" name="requester_email" value="<?php echo htmlspecialchars($request->getRequesterEmail()); ?>" style="width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px;"></td>
                    </tr>
                    <tr>
                        <td><strong>Phone:</strong></td>
                        <td><input type="tel" name="requester_phone" value="<?php echo htmlspecialchars($request->getRequesterPhone()); ?>" style="width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px;"></td>
                    </tr>
                    <tr>
                        <td><strong>Location:</strong></td>
                        <td><input type="text" name="location" value="<?php echo htmlspecialchars($request->getLocation()); ?>" style="width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px;"></td>
                    </tr>
                    <tr>
                        <td><strong>Building:</strong></td>
                        <td><input type="text" name="building" value="<?php echo htmlspecialchars($request->getBuilding()); ?>" style="width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px;"></td>
                    </tr>
                    <tr>
                        <td><strong>Unit:</strong></td>
                        <td><input type="text" name="unit" value="<?php echo htmlspecialchars($request->getUnit()); ?>" style="width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px;"></td>
                    </tr>
                    <tr>
                        <td><strong>Description *:</strong></td>
                        <td><textarea name="description" required style="width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px; height: 50px; resize: vertical; font-size: 13px;"><?php echo htmlspecialchars($request->getDescription()); ?></textarea></td>
                    </tr>
                    <tr>
                        <td><strong>Priority:</strong></td>
                        <td>
                            <select name="priority" style="width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px;">
                                <option value="Low" <?php echo ($request->getPriority() == 'Low') ? 'selected' : ''; ?>>Low</option>
                                <option value="Medium" <?php echo ($request->getPriority() == 'Medium') ? 'selected' : ''; ?>>Medium</option>
                                <option value="High" <?php echo ($request->getPriority() == 'High') ? 'selected' : ''; ?>>High</option>
                                <option value="Emergency" <?php echo ($request->getPriority() == 'Emergency') ? 'selected' : ''; ?>>Emergency</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td>
                            <select name="status" style="width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px;">
                                <option value="Pending" <?php echo ($request->getStatus() == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="In Progress" <?php echo ($request->getStatus() == 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                                <option value="Completed" <?php echo ($request->getStatus() == 'Completed') ? 'selected' : ''; ?>>Completed</option>
                                <option value="Cancelled" <?php echo ($request->getStatus() == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Assigned To:</strong></td>
                        <td><input type="text" name="assigned_to" value="<?php echo htmlspecialchars($request->getAssignedTo()); ?>" style="width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px;"></td>
                    </tr>
                    <tr>
                        <td><strong>Created:</strong></td>
                        <td><?php echo date('M j, Y g:i A', strtotime($request->getCreatedAt())); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Notes:</strong></td>
                        <td><textarea name="notes" style="width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px; height: 40px; resize: vertical; font-size: 13px;"><?php echo htmlspecialchars($request->getNotes()); ?></textarea></td>
                    </tr>
                </table>
                
                <div class="form-buttons">
                    <button type="submit" style="background-color: #274471; color: white; border: none; border-radius: 4px; cursor: pointer;">Save Changes</button>
                    <a href="viewAllMaintenanceRequests.php" style="background-color: #6c757d; color: white; border: none; border-radius: 4px; text-decoration: none; display: inline-block;">Cancel</a>
                </div>
                
                </form>
            </div>
        </main>
    </body>
</html>
