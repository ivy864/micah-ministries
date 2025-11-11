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
        header('Location: micahportal.php');
        die();
    }

    // include database functions
    require_once('database/dbMaintenanceRequests.php');
    
    // pagination and sorting settings
    $items_per_page = 15;
    $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    
    // ensure current_page is at least 1
    if ($current_page < 1) {
        $current_page = 1;
    }
    
    $offset = ($current_page - 1) * $items_per_page;
    
    // sorting parameters
    $sort_by = $_GET['sort'] ?? 'created_at';
    $sort_order = $_GET['order'] ?? 'DESC';

    //archive toggle
    $view_archived = filter_input(INPUT_GET, 'archived', FILTER_VALIDATE_INT) === 1;


    
   // get total requests (respect archived flag)
    $total_requests = get_maintenance_requests_count($view_archived);
    $total_pages = ceil($total_requests / $items_per_page);

    // ensure current_page doesn't exceed total pages
    if ($current_page > $total_pages && $total_pages > 0) {
        $current_page = $total_pages;
    }
    $offset = ($current_page - 1) * $items_per_page;

    // get paginated and sorted maintenance requests (respect archived flag)
    $maintenance_requests = get_all_maintenance_requests($items_per_page, $offset, $sort_by, $sort_order, $view_archived);

    // helper function to generate sort URLs
    function getSortUrl($column) {
    global $sort_by, $sort_order, $current_page, $view_archived;
    $new_order = ($sort_by == $column && strtoupper($sort_order) == 'ASC') ? 'DESC' : 'ASC';
    $archived_qs = $view_archived ? '&archived=1' : '';
    return "?page=" . $current_page . "&sort=" . $column . "&order=" . $new_order . $archived_qs;
    }

    
    // helper function to get sort arrow
    function getSortArrow($column) {
        global $sort_by, $sort_order;
        if ($sort_by == $column) {
            return $sort_order == 'ASC' ? ' ↑' : ' ↓';
        }
        return '';
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>     <link rel="icon" type="image/png" href="images/micah-favicon.png">

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View All Maintenance Requests</title>
  <link href="css/base.css?v=<?php echo time(); ?>" rel="stylesheet">

<!-- BANDAID FIX FOR HEADER BEING WEIRD -->
<?php
$tailwind_mode = true;
require_once('header.php');
?>

</head>

<body>

  <!-- Hero Section with Ribbon -->
  <div class="hero-header">
    <div class="center-header">
      <h1><?php echo $view_archived ? 'Archived Maintenance Requests' : 'All Maintenance Requests'; ?></h1>

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
           

          <p class="secondary-text" style="margin-bottom: 20px;">
            View and manage all maintenance requests. Use the table below to see request details, status, and priority levels.
          </p>
        
                    <?php
        // use the current page file name for links (safer if filename changes)
        $self = htmlspecialchars(basename($_SERVER['PHP_SELF']));
        ?>
        <div style="margin-bottom: 15px;">
        <?php if (!$view_archived): ?>
            <a href="<?php echo $self; ?>?archived=1"
            style="background-color: var(--main-color);color:white;padding:8px 15px;border-radius:5px;text-decoration:none;">
            View Archived Requests
            </a>
        <?php else: ?>
            <a href="<?php echo $self; ?>"
            style="background-color: var(--main-color);color:white;padding:8px 15px;border-radius:5px;text-decoration:none;">
            Back to Active Requests
            </a>
        <?php endif; ?>
        </div>


        <?php if (empty($maintenance_requests)): ?>
            <div style="margin-top: 20px; padding: 20px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px;">
                <p><strong>No maintenance requests found.</strong></p>
                <p>Click "Create New Request" to add the first maintenance request.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="sortable-table">
                <thead>
                    <tr>
                        <th><a href="<?php echo getSortUrl('id'); ?>" style="color: #274471; text-decoration: none;">ID<?php echo getSortArrow('id'); ?></a></th>
                        <th><a href="<?php echo getSortUrl('requester_name'); ?>" style="color: #274471; text-decoration: none;">Requester<?php echo getSortArrow('requester_name'); ?></a></th>
                        <th><a href="<?php echo getSortUrl('location'); ?>" style="color: #274471; text-decoration: none;">Location<?php echo getSortArrow('location'); ?></a></th>
                        <th>Description</th>
                        <th><a href="<?php echo getSortUrl('priority'); ?>" style="color: #274471; text-decoration: none;">Priority<?php echo getSortArrow('priority'); ?></a></th>
                        <th><a href="<?php echo getSortUrl('status'); ?>" style="color: #274471; text-decoration: none;">Status<?php echo getSortArrow('status'); ?></a></th>
                        <th><a href="<?php echo getSortUrl('assigned_to'); ?>" style="color: #274471; text-decoration: none;">Assigned To<?php echo getSortArrow('assigned_to'); ?></a></th>
                        <th><a href="<?php echo getSortUrl('created_at'); ?>" style="color: #274471; text-decoration: none;">Created<?php echo getSortArrow('created_at'); ?></a></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($maintenance_requests as $request): ?>
                        <tr>
                            <td><a href="manageMaintenanceRequest.php?id=<?php echo urlencode($request->getID()); ?>" style="color: #274471; text-decoration: none; font-weight: bold;"><?php echo htmlspecialchars($request->getID()); ?></a></td>
                            <td>
                                <?php echo htmlspecialchars($request->getRequesterName()); ?><br>
                                <small><?php echo htmlspecialchars($request->getRequesterEmail()); ?></small><br>
                                <small style="color: #666;"><?php echo htmlspecialchars($request->getRequesterPhone()); ?></small>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($request->getLocation()); ?>
                                <?php if ($request->getBuilding()): ?>
                                    <br><small>Building: <?php echo htmlspecialchars($request->getBuilding()); ?></small>
                                <?php endif; ?>
                                <?php if ($request->getUnit()): ?>
                                    <br><small>Unit: <?php echo htmlspecialchars($request->getUnit()); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars(substr($request->getDescription(), 0, 100)) . (strlen($request->getDescription()) > 100 ? '...' : ''); ?></td>
                            <td>
                                <span class="priority-<?php echo strtolower($request->getPriority()); ?>">
                                    <?php echo htmlspecialchars($request->getPriority()); ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-<?php echo strtolower(str_replace(' ', '-', $request->getStatus())); ?>">
                                    <?php echo htmlspecialchars($request->getStatus()); ?>
                                </span>
                                <?php if ($view_archived): ?>
                                    <small style="color:#6c757d;margin-left:6px;">(archived)</small>
                                <?php endif; ?>
                            </td>

                            <td><?php echo htmlspecialchars($request->getAssignedTo() ?: 'Unassigned'); ?></td>
                            <td><?php echo date('M j, Y', strtotime($request->getCreatedAt())); ?></td>
<td style="display:flex;gap:10px;align-items:center;height:100%;justify-content:flex-start;flex-wrap:nowrap;white-space:nowrap;">
    <?php if (!$view_archived): ?>
        <!-- Start (only for Pending requests) -->
        <?php if (strtolower($request->getStatus()) === 'pending' && ($accessLevel == 1 || $accessLevel == 2 || $accessLevel == 3)): ?>
            <a href="startMaintenanceRequest.php?id=<?php echo urlencode($request->getID()); ?>"
               style="background-color:#007bff;color:white;padding:6px 12px;border-radius:4px;
                      text-decoration:none;font-weight:bold;flex-shrink:0;">
               Start
            </a>
        <?php endif; ?>

        <!-- Mark Complete -->
        <a href="completeMaintenanceRequest.php?id=<?php echo urlencode($request->getID()); ?>"
           style="background-color:#28a745;color:white;padding:6px 12px;border-radius:4px;
                  text-decoration:none;font-weight:bold;flex-shrink:0;">
           Mark Complete
        </a>

        <!-- Edit -->
        <a href="manageMaintenanceRequest.php?id=<?php echo urlencode($request->getID()); ?>&edit=1"
           style="background-color:#6c757d;color:white;padding:6px 12px;border-radius:4px;
                  text-decoration:none;font-weight:bold;transition:background-color 0.2s;flex-shrink:0;"
           onmouseover="this.style.backgroundColor='#5a6268';"
           onmouseout="this.style.backgroundColor='#6c757d';">
           Edit
        </a>

        <!-- Delete -->
        <a href="deleteMaintenanceRequest.php?id=<?php echo urlencode($request->getID()); ?>"
           style="background-color:#dc3545;color:white;padding:6px 12px;border-radius:4px;
                  text-decoration:none;font-weight:bold;flex-shrink:0;">
           Delete
        </a>

        <!-- Archive -->
        <a href="archiveMaintenanceRequest.php?id=<?php echo urlencode($request->getID()); ?>"
           style="background-color:#274471;color:white;padding:6px 12px;border-radius:4px;
                  text-decoration:none;font-weight:bold;flex-shrink:0;">
           Archive
        </a>
    <?php else: ?>
        <!-- Edit -->
        <a href="manageMaintenanceRequest.php?id=<?php echo urlencode($request->getID()); ?>&edit=1"
           style="background-color:#6c757d;color:white;padding:6px 12px;border-radius:4px;
                  text-decoration:none;font-weight:bold;transition:background-color 0.2s;flex-shrink:0;"
           onmouseover="this.style.backgroundColor='#5a6268';"
           onmouseout="this.style.backgroundColor='#6c757d';">
           Edit
        </a>

        <!-- Delete (Archived view: permanent delete) -->
        <a href="deleteMaintenanceRequest.php?id=<?php echo urlencode($request->getID()); ?>"
           style="background-color:#dc3545;color:white;padding:6px 12px;border-radius:4px;
                  text-decoration:none;font-weight:bold;flex-shrink:0;">
           Delete
        </a>

        <!-- Restore -->
        <a href="restoreMaintenanceRequest.php?id=<?php echo urlencode($request->getID()); ?>"
           style="background-color:#274471;color:white;padding:6px 12px;border-radius:4px;
                  text-decoration:none;font-weight:bold;flex-shrink:0;">
           Restore
        </a>
    <?php endif; ?>
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
                    
                        <?php $arch = $view_archived ? '&archived=1' : ''; ?>

                    <div class="pagination-buttons">
                        <?php if ($current_page > 1): ?>
                            <a href="?page=1&sort=<?php echo $sort_by; ?>&order=<?php echo $sort_order; ?><?php echo $arch; ?>" class="pagination-btn" style="display: inline-block; padding: 8px 12px; margin: 0 2px; background: #274471; color: white; text-decoration: none; border-radius: 4px;">First</a>
                            <a href="?page=<?php echo $current_page - 1; ?>&sort=<?php echo $sort_by; ?>&order=<?php echo $sort_order; ?><?php echo $arch; ?>" class="pagination-btn" style="display: inline-block; padding: 8px 12px; margin: 0 2px; background: #274471; color: white; text-decoration: none; border-radius: 4px;">Previous</a>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $current_page - 2);
                        $end_page = min($total_pages, $current_page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                            <a href="?page=<?php echo $i; ?>&sort=<?php echo $sort_by; ?>&order=<?php echo $sort_order; ?><?php echo $arch; ?>" class="pagination-btn <?php echo $i == $current_page ? 'active' : ''; ?>" 
                               style="display: inline-block; padding: 8px 12px; margin: 0 2px; background: <?php echo $i == $current_page ? '#1e3554' : '#274471'; ?>; color: white; text-decoration: none; border-radius: 4px;">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($current_page < $total_pages): ?>
                            <a href="?page=<?php echo $current_page + 1; ?>&sort=<?php echo $sort_by; ?>&order=<?php echo $sort_order; ?><?php echo $arch; ?>" class="pagination-btn" style="display: inline-block; padding: 8px 12px; margin: 0 2px; background: #274471; color: white; text-decoration: none; border-radius: 4px;">Next</a>
                            <a href="?page=<?php echo $total_pages; ?>&sort=<?php echo $sort_by; ?>&order=<?php echo $sort_order; ?><?php echo $arch; ?>" class="pagination-btn" style="display: inline-block; padding: 8px 12px; margin: 0 2px; background: #274471; color: white; text-decoration: none; border-radius: 4px;">Last</a>
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