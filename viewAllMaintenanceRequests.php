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
    
    // pagination settings
    $items_per_page = 15;
    $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($current_page - 1) * $items_per_page;
    
    // get paginated maintenance requests
    $maintenance_requests = get_all_maintenance_requests($items_per_page, $offset);
    $total_requests = get_maintenance_requests_count();
    $total_pages = ceil($total_requests / $items_per_page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View All Maintenance Requests</title>
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
	
	/* full width layout overrides */
	.sections {
	    flex-direction: row !important;
	    gap: 10px !important;
	}
	
	.button-section {
	    width: 0% !important;
	    display: none !important;
	}
	
	.text-section {
	    width: 100% !important;
	}
	
	/* adjust main content with hero - closer spacing */
	main {
	    margin-top: calc(var(--spacing) * -20) !important;
	    padding: 10px !important;
	}
	
	/* make hero header shorter to move heading closer to content */
	.hero-header {
	    height: calc(var(--spacing) * 50) !important;
	}
	
	/* compact text section */
	.text-section h1 {
	    margin-bottom: 5px !important;
	}
	
	.text-section p {
	    margin-bottom: 10px !important;
	}
	
	/* table improvements */
	.maintenance-table {
	    width: 100% !important;
	    margin-top: 10px !important;
	}
	
	.maintenance-table th,
	.maintenance-table td {
	    padding: 6px !important;
	    font-size: 13px !important;
	}
	
	/* ensure table fits in box */
	.overflow-x-auto {
	    overflow-x: auto !important;
	}
	
	/* ensure center header text is white and centered */
	.center-header h1 {
	    color: white !important;
	    font-size: 2rem !important;
	    font-weight: bold !important;
	    text-align: center !important;
	    margin-top: 5rem !important;
	}
	
	/* ensure main-content-box styling is applied */
	.main-content-box {
	    background-color: white !important;
	    border: 2px solid #d1d5db !important;
	    border-radius: 0.75rem !important;
	    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1) !important;
	    padding: 1.5rem !important;
	}

</style>
<!-- BANDAID END, REMOVE ONCE SOME GENIUS FIXES -->

</head>

<body>

  <!-- Hero Section with Ribbon -->
  <div class="hero-header">
    <div class="center-header">
      <h1>All Maintenance Requests</h1>
    </div>
  </div>

  <!-- Main Content -->
  <main>
    <div class="sections">

      <!-- Navigation Section - Removed -->
      <div class="button-section">
     </div>

      <!-- Text Section -->
      <div class="text-section">
        <div class="main-content-box p-6">
          <p style="margin-bottom: 20px;">
            View and manage all maintenance requests. Use the table below to see request details, status, and priority levels.
          </p>
        
        <?php if (empty($maintenance_requests)): ?>
            <div style="margin-top: 20px; padding: 20px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px;">
                <p><strong>No maintenance requests found.</strong></p>
                <p>Click "Create New Request" to add the first maintenance request.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
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
            </div>
            
            <!-- Pagination Controls -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination-container" style="margin-top: 20px; text-align: center;">
                    <div class="pagination-info" style="margin-bottom: 10px; color: #666;">
                        Showing <?php echo $offset + 1; ?>-<?php echo min($offset + $items_per_page, $total_requests); ?> of <?php echo $total_requests; ?> requests
                    </div>
                    
                    <div class="pagination-buttons">
                        <?php if ($current_page > 1): ?>
                            <a href="?page=1" class="pagination-btn" style="display: inline-block; padding: 8px 12px; margin: 0 2px; background: #274471; color: white; text-decoration: none; border-radius: 4px;">First</a>
                            <a href="?page=<?php echo $current_page - 1; ?>" class="pagination-btn" style="display: inline-block; padding: 8px 12px; margin: 0 2px; background: #274471; color: white; text-decoration: none; border-radius: 4px;">Previous</a>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $current_page - 2);
                        $end_page = min($total_pages, $current_page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                            <a href="?page=<?php echo $i; ?>" class="pagination-btn <?php echo $i == $current_page ? 'active' : ''; ?>" 
                               style="display: inline-block; padding: 8px 12px; margin: 0 2px; background: <?php echo $i == $current_page ? '#1e3554' : '#274471'; ?>; color: white; text-decoration: none; border-radius: 4px;">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($current_page < $total_pages): ?>
                            <a href="?page=<?php echo $current_page + 1; ?>" class="pagination-btn" style="display: inline-block; padding: 8px 12px; margin: 0 2px; background: #274471; color: white; text-decoration: none; border-radius: 4px;">Next</a>
                            <a href="?page=<?php echo $total_pages; ?>" class="pagination-btn" style="display: inline-block; padding: 8px 12px; margin: 0 2px; background: #274471; color: white; text-decoration: none; border-radius: 4px;">Last</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        </div>
      </div>

    </div>
  </main>
</body>
</html>
