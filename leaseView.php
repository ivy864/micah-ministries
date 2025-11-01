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
    require_once('database/dbinfo.php');
    
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
    
    // get total leases first to validate page number
    $con = connect();
    $count_query = "SELECT COUNT(*) as total FROM dbleases";
    $count_result = mysqli_query($con, $count_query);
    $total_leases = mysqli_fetch_assoc($count_result)['total'];
    $total_pages = ceil($total_leases / $items_per_page);
    
    // ensure current_page doesn't exceed total pages
    if ($current_page > $total_pages && $total_pages > 0) {
        $current_page = $total_pages;
        $offset = ($current_page - 1) * $items_per_page;
    }
    
    // get paginated and sorted leases
    $leases = [];
    if ($con) {
        $query = "SELECT * FROM dbleases ORDER BY $sort_by $sort_order LIMIT $items_per_page OFFSET $offset";
        $result = mysqli_query($con, $query);
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $leases[] = $row;
            }
        }
        mysqli_close($con);
    }
    
    // helper function to generate sort URLs
    function getSortUrl($column) {
        global $sort_by, $sort_order, $current_page;
        $new_order = ($sort_by == $column && $sort_order == 'ASC') ? 'DESC' : 'ASC';
        return "?page=" . $current_page . "&sort=" . $column . "&order=" . $new_order;
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
  <title>View All Leases</title>
  <link href="css/management_tw.css?v=<?php echo time(); ?>" rel="stylesheet">
  <link href="css/normal_tw.css?v=<?php echo time(); ?>" rel="stylesheet">

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
	
	.lease-table {
	    width: 100%;
	    border-collapse: collapse;
	    margin-top: 20px;
	}
	
	.lease-table th,
	.lease-table td {
	    border: 1px solid #ddd;
	    padding: 8px;
	    text-align: left;
	}
	
	.lease-table th {
	    background-color: #274471;
	    color: white;
	    font-weight: bold;
	}
	
	.lease-table th a {
	    color: white !important;
	    text-decoration: none;
	    display: block;
	    width: 100%;
	}
	
	.lease-table th a:hover {
	    color: #ffd700 !important;
	    text-decoration: underline;
	}
	
	.lease-table td a {
	    color: #274471;
	    text-decoration: none;
	    font-weight: bold;
	}
	
	.lease-table td a:hover {
	    color: #1e3554;
	    text-decoration: underline;
	}
	
	.lease-table tr:nth-child(even) {
	    background-color: #f2f2f2;
	}
	
	.status-active {
	    color: #28a745;
	    font-weight: bold;
	}
	
	.status-expired {
	    color: #dc3545;
	    font-weight: bold;
	}
	
	.status-terminated {
	    color: #6c757d;
	    font-weight: bold;
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
	.lease-table {
	    width: 100% !important;
	    margin-top: 10px !important;
	}
	
	.lease-table th,
	.lease-table td {
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
      <h1>All Leases</h1>
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
            View and manage all leases. Use the table below to see lease details, tenant information, and property details.
          </p>
        
        <?php if (empty($leases)): ?>
            <div style="margin-top: 20px; padding: 20px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px;">
                <p><strong>No leases found.</strong></p>
                <p>Click "Add a Lease" to add the first lease.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="lease-table">
                <thead>
                    <tr>
                        <th><a href="<?php echo getSortUrl('id'); ?>" style="color: #274471; text-decoration: none;">Lease ID<?php echo getSortArrow('id'); ?></a></th>
                        <th><a href="<?php echo getSortUrl('tenant_first_name'); ?>" style="color: #274471; text-decoration: none;">Tenant Name<?php echo getSortArrow('tenant_first_name'); ?></a></th>
                        <th><a href="<?php echo getSortUrl('property_street'); ?>" style="color: #274471; text-decoration: none;">Property Address<?php echo getSortArrow('property_street'); ?></a></th>
                        <th><a href="<?php echo getSortUrl('unit_number'); ?>" style="color: #274471; text-decoration: none;">Unit<?php echo getSortArrow('unit_number'); ?></a></th>
                        <th><a href="<?php echo getSortUrl('program_type'); ?>" style="color: #274471; text-decoration: none;">Program Type<?php echo getSortArrow('program_type'); ?></a></th>
                        <th><a href="<?php echo getSortUrl('expiration_date'); ?>" style="color: #274471; text-decoration: none;">Expiration Date<?php echo getSortArrow('expiration_date'); ?></a></th>
                        <th><a href="<?php echo getSortUrl('status'); ?>" style="color: #274471; text-decoration: none;">Status<?php echo getSortArrow('status'); ?></a></th>
                        <th><a href="<?php echo getSortUrl('monthly_rent'); ?>" style="color: #274471; text-decoration: none;">Monthly Rent<?php echo getSortArrow('monthly_rent'); ?></a></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leases as $lease): ?>
                        <tr>
                            <td><a href="editLease.php?id=<?php echo urlencode($lease['id']); ?>" style="color: #274471; text-decoration: none; font-weight: bold;"><?php echo htmlspecialchars($lease['id']); ?></a></td>
                            <td>
                                <?php echo htmlspecialchars($lease['tenant_first_name'] . ' ' . $lease['tenant_last_name']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($lease['property_street']); ?><br>
                                <small><?php echo htmlspecialchars($lease['property_city'] . ', ' . $lease['property_state'] . ' ' . $lease['property_zip']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($lease['unit_number']); ?></td>
                            <td><?php echo htmlspecialchars($lease['program_type'] ?: 'N/A'); ?></td>
                            <td><?php echo date('M j, Y', strtotime($lease['expiration_date'])); ?></td>
                            <td>
                                <span class="status-<?php echo strtolower($lease['status']); ?>">
                                    <?php echo htmlspecialchars($lease['status']); ?>
                                </span>
                            </td>
                            <td><?php echo $lease['monthly_rent'] ? '$' . number_format($lease['monthly_rent'], 2) : 'N/A'; ?></td>
                            <td>
                                <a href="editLease.php?id=<?php echo urlencode($lease['id']); ?>" class="return-button">Edit</a>
                                <a href="deletelease.php?id=<?php echo urlencode($lease['id']); ?>" class="delete-button">Delete</a>
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
                        Showing <?php echo $offset + 1; ?>-<?php echo min($offset + $items_per_page, $total_leases); ?> of <?php echo $total_leases; ?> leases
                    </div>
                    
                    <div class="pagination-buttons">
                        <?php if ($current_page > 1): ?>
                            <a href="?page=1&sort=<?php echo $sort_by; ?>&order=<?php echo $sort_order; ?>" class="pagination-btn" style="display: inline-block; padding: 8px 12px; margin: 0 2px; background: #274471; color: white; text-decoration: none; border-radius: 4px;">First</a>
                            <a href="?page=<?php echo $current_page - 1; ?>&sort=<?php echo $sort_by; ?>&order=<?php echo $sort_order; ?>" class="pagination-btn" style="display: inline-block; padding: 8px 12px; margin: 0 2px; background: #274471; color: white; text-decoration: none; border-radius: 4px;">Previous</a>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $current_page - 2);
                        $end_page = min($total_pages, $current_page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                            <a href="?page=<?php echo $i; ?>&sort=<?php echo $sort_by; ?>&order=<?php echo $sort_order; ?>" class="pagination-btn <?php echo $i == $current_page ? 'active' : ''; ?>" 
                               style="display: inline-block; padding: 8px 12px; margin: 0 2px; background: <?php echo $i == $current_page ? '#1e3554' : '#274471'; ?>; color: white; text-decoration: none; border-radius: 4px;">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($current_page < $total_pages): ?>
                            <a href="?page=<?php echo $current_page + 1; ?>&sort=<?php echo $sort_by; ?>&order=<?php echo $sort_order; ?>" class="pagination-btn" style="display: inline-block; padding: 8px 12px; margin: 0 2px; background: #274471; color: white; text-decoration: none; border-radius: 4px;">Next</a>
                            <a href="?page=<?php echo $total_pages; ?>&sort=<?php echo $sort_by; ?>&order=<?php echo $sort_order; ?>" class="pagination-btn" style="display: inline-block; padding: 8px 12px; margin: 0 2px; background: #274471; color: white; text-decoration: none; border-radius: 4px;">Last</a>
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