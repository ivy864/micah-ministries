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
    
    // get all maintenance requests
    $maintenance_requests = get_all_maintenance_requests();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View All Maintenance Requests</title>
  <link href="css/management_tw.css" rel="stylesheet">

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
	
	.maintenance-table {
	    width: 100%;
	    border-collapse: collapse;
	    margin-top: 20px;
	}
	
	.maintenance-table th,
	.maintenance-table td {
	    border: 1px solid #ddd;
	    padding: 8px;
	    text-align: left;
	}
	
	.maintenance-table th {
	    background-color: #274471;
	    color: white;
	    font-weight: bold;
	}
	
	.maintenance-table tr:nth-child(even) {
	    background-color: #f2f2f2;
	}
	
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

</style>
<!-- BANDAID END, REMOVE ONCE SOME GENIUS FIXES -->

</head>

<body>

  <!-- Larger Hero Section -->
  <header class="hero-header"></header>

  <!-- Main Content -->
  <main>
    <div class="sections">

      <!-- Buttons Section -->
      <div class="button-section">
        <button onclick="window.location.href='maintman.php';">
	  <div class="button-left-gray"></div>
	  <div>Back to Maintenance Management</div>
	  <img class="button-icon h-10 w-10 left-5" src="images/arrow-left-solid.svg" alt="Back Icon">
        </button>

        <button onclick="window.location.href='addMaintenanceRequest.php';">
	  <div class="button-left-gray"></div>
	  <div>Create New Request</div>
	  <img class="button-icon h-12 w-12 left-4" src="images/plus-solid.svg" alt="Plus Icon">
        </button>

	<div class="text-center mt-6">
        	<a href="index.php" class="return-button">Return to Dashboard</a>
	</div>

     </div>

      <!-- Text Section -->
      <div class="text-section">
        <h1>All Maintenance Requests</h1>
        <div class="div-blue"></div>
        <p>
          View and manage all maintenance requests. Use the table below to see request details, status, and priority levels.
        </p>
        
        <?php if (empty($maintenance_requests)): ?>
            <div style="margin-top: 20px; padding: 20px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px;">
                <p><strong>No maintenance requests found.</strong></p>
                <p>Click "Create New Request" to add the first maintenance request.</p>
            </div>
        <?php else: ?>
            <table class="maintenance-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Requester</th>
                        <th>Location</th>
                        <th>Description</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($maintenance_requests as $request): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($request['id']); ?></td>
                            <td>
                                <?php echo htmlspecialchars($request['requester_name']); ?><br>
                                <small><?php echo htmlspecialchars($request['requester_email']); ?></small>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($request['location']); ?>
                                <?php if ($request['building']): ?>
                                    <br><small>Building: <?php echo htmlspecialchars($request['building']); ?></small>
                                <?php endif; ?>
                                <?php if ($request['unit']): ?>
                                    <br><small>Unit: <?php echo htmlspecialchars($request['unit']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars(substr($request['description'], 0, 100)) . (strlen($request['description']) > 100 ? '...' : ''); ?></td>
                            <td>
                                <span class="priority-<?php echo strtolower($request['priority']); ?>">
                                    <?php echo htmlspecialchars($request['priority']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-<?php echo strtolower(str_replace(' ', '-', $request['status'])); ?>">
                                    <?php echo htmlspecialchars($request['status']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($request['assigned_to'] ?: 'Unassigned'); ?></td>
                            <td><?php echo date('M j, Y', strtotime($request['created_at'])); ?></td>
                            <td>
                                <a href="editMaintenanceRequest.php?id=<?php echo urlencode($request['id']); ?>" style="color: #274471; text-decoration: none;">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
      </div>

    </div>
  </main>
</body>
</html>
