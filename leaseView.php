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
    if ($current_page < 1) { $current_page = 1; }
    $offset = ($current_page - 1) * $items_per_page;
    
    // sorting parameters - validate to prevent sql injection
    $allowed_sort_columns = ['id', 'tenant_first_name', 'tenant_last_name', 'property_street', 'property_city', 'property_state', 'unit_number', 'start_date', 'expiration_date', 'monthly_rent', 'security_deposit', 'program_type', 'status', 'created_at', 'updated_at'];
    $sort_by = $_GET['sort'] ?? 'created_at';
    if (!in_array($sort_by, $allowed_sort_columns)) {
        $sort_by = 'created_at';
    }
    
    $sort_order = strtoupper($_GET['order'] ?? 'DESC');
    if (!in_array($sort_order, ['ASC', 'DESC'])) {
        $sort_order = 'DESC';
    }

    // NEW: month filter (1..12). Keep empty => all months
    $exp_month = $_GET['exp_month'] ?? '';
    $month_int = null;
    if ($exp_month !== '' && ctype_digit($exp_month)) {
        $m = (int)$exp_month;
        if ($m >= 1 && $m <= 12) { $month_int = $m; }
    }

    // Build WHERE for month filter
    $where = '';
    if ($month_int !== null) {
        $where = " WHERE MONTH(expiration_date) = " . $month_int;
    }
    
    // get total leases first to validate page number (with filter)
    $con = connect();
    $total_leases = 0;
    if ($con) {
        $count_query = "SELECT COUNT(*) as total FROM dbleases" . $where;
        $count_result = mysqli_query($con, $count_query);
        if ($count_result) {
            $total_leases = (int)mysqli_fetch_assoc($count_result)['total'];
        }
    }
    $total_pages = $items_per_page > 0 ? (int)ceil($total_leases / $items_per_page) : 1;
    
    // ensure current_page doesn't exceed total pages
    if ($current_page > $total_pages && $total_pages > 0) {
        $current_page = $total_pages;
        $offset = ($current_page - 1) * $items_per_page;
    }
    
    // get paginated and sorted leases (with filter)
    $leases = [];
    if ($con) {
        $query = "SELECT * FROM dbleases" . $where . " ORDER BY " . $sort_by . " " . $sort_order . " LIMIT " . (int)$items_per_page . " OFFSET " . (int)$offset;
        $result = mysqli_query($con, $query);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) { $leases[] = $row; }
        }
        mysqli_close($con);
    }
    
    // helper: keep month param in links
    function monthQS() {
        return (isset($_GET['exp_month']) && $_GET['exp_month'] !== '')
            ? "&exp_month=" . urlencode($_GET['exp_month'])
            : "";
    }

    // helper function to generate sort URLs (preserve month + page)
    function getSortUrl($column) {
        global $sort_by, $sort_order, $current_page;
        $new_order = ($sort_by == $column && $sort_order == 'ASC') ? 'DESC' : 'ASC';
        return "?page=" . $current_page . "&sort=" . $column . "&order=" . $new_order . monthQS();
    }
    
    // helper function to get sort arrow
    function getSortArrow($column) {
        global $sort_by, $sort_order;
        if ($sort_by == $column) {
            return $sort_order == 'ASC' ? ' ↑' : ' ↓';
        }
        return '';
    }

    // month names for dropdown
    $month_names = [
        1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',
        7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'
    ];
?>

<!DOCTYPE html>
<html lang="en">
<head>     
    
  <link rel="icon" type="image/png" href="images/micah-favicon.png">
  <link href="css/base.css?v=<?php echo time(); ?>" rel="stylesheet">

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View All Leases</title>
 

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
          <p class="secondary-text" style="margin-bottom: 10px; text-align: left;">
            View and manage all leases. Use the table below to see lease details, tenant information, and property details.
          </p>

          <!-- NEW: Month filter (adds on top of existing features; preserves look) -->
          <form class="filter-bar" method="get">
            <!-- preserve current sort when filtering -->
            <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort_by, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="order" value="<?php echo htmlspecialchars($sort_order, ENT_QUOTES, 'UTF-8'); ?>">
            <!-- when applying a filter, start at page 1 -->
            <input type="hidden" name="page" value="1">

            <label for="exp_month">Expires in (month):</label>
            <select id="exp_month" name="exp_month">
              <option value="">All months</option>
              <?php foreach ($month_names as $num=>$name): ?>
                <option value="<?php echo $num; ?>" <?php echo ($month_int === $num ? 'selected' : ''); ?>>
                  <?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>
                </option>
              <?php endforeach; ?>
            </select>

            <button class="apply-btn" type="submit">Apply</button>
            <a class="reset-btn" href="?page=1&sort=<?php echo urlencode($sort_by); ?>&order=<?php echo urlencode($sort_order); ?>">Reset</a>
          </form>
        
        <?php if (empty($leases)): ?>
            <div style="margin-top: 10px; padding: 14px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px;">
                <p><strong>No leases found.</strong></p>
                <p>Try a different month, or click “Reset”.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="sortable-table">
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
                                <a href="editLease.php?id=<?php echo urlencode($lease['id']); ?>" class="blue-button" style="color: white;">Edit</a>
                                <a href="deletelease.php?id=<?php echo urlencode($lease['id']); ?>" class="delete-button" style="color: white;">Delete</a>
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
                            <a href="?page=1&sort=<?php echo $sort_by; ?>&order=<?php echo $sort_order; ?><?php echo monthQS(); ?>" class="pagination-btn" style="display: inline-block; padding: 8px 12px; margin: 0 2px; background: #274471; color: white; text-decoration: none; border-radius: 4px;">First</a>
                            <a href="?page=<?php echo $current_page - 1; ?>&sort=<?php echo $sort_by; ?>&order=<?php echo $sort_order; ?><?php echo monthQS(); ?>" class="pagination-btn" style="display: inline-block; padding: 8px 12px; margin: 0 2px; background: #274471; color: white; text-decoration: none; border-radius: 4px;">Previous</a>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $current_page - 2);
                        $end_page = min($total_pages, $current_page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                            <a href="?page=<?php echo $i; ?>&sort=<?php echo $sort_by; ?>&order=<?php echo $sort_order; ?><?php echo monthQS(); ?>" class="pagination-btn <?php echo $i == $current_page ? 'active' : ''; ?>" 
                               style="display: inline-block; padding: 8px 12px; margin: 0 2px; background: <?php echo $i == $current_page ? '#1e3554' : '#274471'; ?>; color: white; text-decoration: none; border-radius: 4px;">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($current_page < $total_pages): ?>
                            <a href="?page=<?php echo $current_page + 1; ?>&sort=<?php echo $sort_by; ?>&order=<?php echo $sort_order; ?><?php echo monthQS(); ?>" class="pagination-btn" style="display: inline-block; padding: 8px 12px; margin: 0 2px; background: #274471; color: white; text-decoration: none; border-radius: 4px;">Next</a>
                            <a href="?page=<?php echo $total_pages; ?>&sort=<?php echo $sort_by; ?>&order=<?php echo $sort_order; ?><?php echo monthQS(); ?>" class="pagination-btn" style="display: inline-block; padding: 8px 12px; margin: 0 2px; background: #274471; color: white; text-decoration: none; border-radius: 4px;">Last</a>
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
