<?php
    // Person Search Page - Search for users in the system

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

    // include database functions and domain object
    require_once('database/dbPersons.php');
    require_once('domain/Person.php');
    
    // custom function to get all users
    function getAllUsers() {
        $connection = connect();
        $query = "SELECT * FROM dbpersons WHERE id != 'vmsroot' ORDER BY last_name, first_name";
        $result = mysqli_query($connection, $query);
        
        if (!$result) {
            mysqli_close($connection);
            return [];
        }
        
        $raw = mysqli_fetch_all($result, MYSQLI_ASSOC);
        $persons = [];
        foreach ($raw as $row) {
            $persons[] = make_a_person($row);
        }
        mysqli_close($connection);
        return $persons;
    }
    
    $message = '';
    $error = '';
    $searchResults = null;
    $searchQuery = '';
    $sortColumn = $_GET['sort'] ?? 'name';
    $sortDirection = $_GET['direction'] ?? 'asc';
    
    // sorting function
    function sortUsers($users, $column, $direction) {
        usort($users, function($a, $b) use ($column, $direction) {
            $valueA = '';
            $valueB = '';
            
            switch($column) {
                case 'name':
                    $valueA = strtolower($a->get_first_name() . ' ' . $a->get_last_name());
                    $valueB = strtolower($b->get_first_name() . ' ' . $b->get_last_name());
                    break;
                case 'id':
                    $valueA = strtolower($a->get_id());
                    $valueB = strtolower($b->get_id());
                    break;
                case 'email':
                    $valueA = strtolower($a->get_email());
                    $valueB = strtolower($b->get_email());
                    break;
                case 'phone':
                    $valueA = $a->get_phone1();
                    $valueB = $b->get_phone1();
                    break;
                case 'type':
                    $valueA = strtolower($a->get_type());
                    $valueB = strtolower($b->get_type());
                    break;
                case 'status':
                    $valueA = strtolower($a->get_status());
                    $valueB = strtolower($b->get_status());
                    break;
                default:
                    return 0;
            }
            
            if ($direction === 'asc') {
                return strcmp($valueA, $valueB);
            } else {
                return strcmp($valueB, $valueA);
            }
        });
        
        return $users;
    }
    
    // determine which users to display in the table
    $usersToDisplay = getAllUsers(); // default to all users
    $tableTitle = "All Users (" . count($usersToDisplay) . " total)";
    
    // handle form submission or search parameters from sorting
    $searchParams = [];
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $searchParams = $_POST;
    } elseif (!empty($_GET['name']) || !empty($_GET['id']) || !empty($_GET['phone']) || !empty($_GET['zip']) || !empty($_GET['type']) || !empty($_GET['status'])) {
        $searchParams = $_GET;
    }
    
    if (!empty($searchParams)) {
        $name = trim($searchParams['name'] ?? '');
        $id = trim($searchParams['id'] ?? '');
        $phone = trim($searchParams['phone'] ?? '');
        $zip = trim($searchParams['zip'] ?? '');
        $type = $searchParams['type'] ?? '';
        $status = $searchParams['status'] ?? '';
        
        // basic validation - at least one search field required
        if (empty($name) && empty($id) && empty($phone) && empty($zip) && empty($type) && empty($status)) {
            $error = 'Please enter at least one search criteria.';
        } else {
            // perform search using the find_users function
            $searchResults = find_users($name, $id, $phone, $zip, $type, $status);
            
            if (empty($searchResults)) {
                $message = 'No users found matching your search criteria.';
                $usersToDisplay = []; // show empty table
                $tableTitle = "Search Results (0 found)";
            } else {
                $usersToDisplay = $searchResults; // show search results in main table
                $tableTitle = "Search Results (" . count($searchResults) . " found)";
            }
        }
    }
    
    // apply sorting to users
    if (!empty($usersToDisplay)) {
        $usersToDisplay = sortUsers($usersToDisplay, $sortColumn, $sortDirection);
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="images/micah-favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Users - Micah Ministries</title>
    <link href="css/management_tw.css?v=<?php echo time(); ?>" rel="stylesheet">

<!-- BANDAID FIX FOR HEADER BEING WEIRD -->
<?php
$tailwind_mode = true;
require_once('header.php');
?>
    
    <main>
        <div class="sections">
            <div class="text-section">
                <h1>Search Users</h1>
                <p>Search for users in the system by name, ID, phone, zip code, type, or status.</p>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <div class="form-container">
                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($searchParams['name'] ?? ''); ?>" 
                                   placeholder="First name, last name, or both">
                        </div>
                        <div class="form-group">
                            <label for="id">User ID</label>
                            <input type="text" id="id" name="id" 
                                   value="<?php echo htmlspecialchars($searchParams['id'] ?? ''); ?>" 
                                   placeholder="Username or partial ID">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="text" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($searchParams['phone'] ?? ''); ?>" 
                                   placeholder="Phone number">
                        </div>
                        <div class="form-group">
                            <label for="zip">Zip Code</label>
                            <input type="text" id="zip" name="zip" 
                                   value="<?php echo htmlspecialchars($searchParams['zip'] ?? ''); ?>" 
                                   placeholder="Zip code">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="type">User Type</label>
                            <select id="type" name="type">
                                <option value="">All Types</option>
                                <option value="admin" <?php echo ($searchParams['type'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                <option value="case_manager" <?php echo ($searchParams['type'] ?? '') === 'case_manager' ? 'selected' : ''; ?>>Case Manager</option>
                                <option value="maintenance" <?php echo ($searchParams['type'] ?? '') === 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                                <option value="volunteer" <?php echo ($searchParams['type'] ?? '') === 'volunteer' ? 'selected' : ''; ?>>Volunteer</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="Active" <?php echo ($searchParams['status'] ?? '') === 'Active' ? 'selected' : ''; ?>>Active</option>
                                <option value="Inactive" <?php echo ($searchParams['status'] ?? '') === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    
                    <div style="text-align: center; margin-top: 30px;">
                        <button type="submit" class="btn-primary">Search Users</button>
                        <a href="personSearch.php" class="btn-secondary" style="margin-left: 10px;">Clear Search</a>
                    </div>
                </form>
            </div>
            
            <!-- Users Table - Shows All Users or Search Results -->
            <div class="main-content-box" style="width: 100%; margin-top: 20px;">
                <h3 style="margin-bottom: 15px; color: #274471;"><?php echo htmlspecialchars($tableTitle); ?></h3>
                
                <?php if (empty($usersToDisplay)): ?>
                    <div style="margin-top: 20px; padding: 20px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px;">
                        <p><strong>No users found.</strong></p>
                        <?php if ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
                            <p>Try adjusting your search criteria or <a href="personSearch.php">clear the search</a> to see all users.</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="users-table">
                            <thead>
                                <tr>
                                    <th>
                                        <a href="?sort=name&direction=<?php echo ($sortColumn === 'name' && $sortDirection === 'asc') ? 'desc' : 'asc'; ?><?php echo !empty($searchParams) ? '&' . http_build_query($searchParams) : ''; ?>" class="sort-link">
                                            Name
                                            <?php if ($sortColumn === 'name'): ?>
                                                <span class="sort-indicator"><?php echo $sortDirection === 'asc' ? '↑' : '↓'; ?></span>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="?sort=id&direction=<?php echo ($sortColumn === 'id' && $sortDirection === 'asc') ? 'desc' : 'asc'; ?><?php echo !empty($searchParams) ? '&' . http_build_query($searchParams) : ''; ?>" class="sort-link">
                                            User ID
                                            <?php if ($sortColumn === 'id'): ?>
                                                <span class="sort-indicator"><?php echo $sortDirection === 'asc' ? '↑' : '↓'; ?></span>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="?sort=email&direction=<?php echo ($sortColumn === 'email' && $sortDirection === 'asc') ? 'desc' : 'asc'; ?><?php echo !empty($searchParams) ? '&' . http_build_query($searchParams) : ''; ?>" class="sort-link">
                                            Email
                                            <?php if ($sortColumn === 'email'): ?>
                                                <span class="sort-indicator"><?php echo $sortDirection === 'asc' ? '↑' : '↓'; ?></span>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="?sort=phone&direction=<?php echo ($sortColumn === 'phone' && $sortDirection === 'asc') ? 'desc' : 'asc'; ?><?php echo !empty($searchParams) ? '&' . http_build_query($searchParams) : ''; ?>" class="sort-link">
                                            Phone
                                            <?php if ($sortColumn === 'phone'): ?>
                                                <span class="sort-indicator"><?php echo $sortDirection === 'asc' ? '↑' : '↓'; ?></span>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="?sort=type&direction=<?php echo ($sortColumn === 'type' && $sortDirection === 'asc') ? 'desc' : 'asc'; ?><?php echo !empty($searchParams) ? '&' . http_build_query($searchParams) : ''; ?>" class="sort-link">
                                            Type
                                            <?php if ($sortColumn === 'type'): ?>
                                                <span class="sort-indicator"><?php echo $sortDirection === 'asc' ? '↑' : '↓'; ?></span>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="?sort=status&direction=<?php echo ($sortColumn === 'status' && $sortDirection === 'asc') ? 'desc' : 'asc'; ?><?php echo !empty($searchParams) ? '&' . http_build_query($searchParams) : ''; ?>" class="sort-link">
                                            Status
                                            <?php if ($sortColumn === 'status'): ?>
                                                <span class="sort-indicator"><?php echo $sortDirection === 'asc' ? '↑' : '↓'; ?></span>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usersToDisplay as $user): ?>
                                    <tr>
                                        <td>
                                            <?php echo htmlspecialchars($user->get_first_name() . ' ' . $user->get_last_name()); ?>
                                        </td>
                                        <td>
                                            <a href="viewProfile.php?id=<?php echo urlencode($user->get_id()); ?>" style="color: #274471; text-decoration: none; font-weight: bold;">
                                                <?php echo htmlspecialchars($user->get_id()); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars($user->get_email()); ?></td>
                                        <td><?php echo htmlspecialchars($user->get_phone1()); ?></td>
                                        <td>
                                            <span class="type-<?php echo strtolower(str_replace('_', '-', $user->get_type())); ?>">
                                                <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $user->get_type()))); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-<?php echo strtolower($user->get_status()); ?>">
                                                <?php echo htmlspecialchars($user->get_status()); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="viewProfile.php?id=<?php echo urlencode($user->get_id()); ?>" class="profile-link">View Profile</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <style>
        /* adjust main content positioning */
        main {
            margin-top: 80px !important;
            padding: 10px !important;
        }
        
        .sections {
            flex-direction: column !important;
            gap: 20px !important;
        }
        
        .text-section {
            width: 100% !important;
        }
        
        .form-container {
            width: 100% !important;
        }
        
        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .users-table th,
        .users-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        .users-table th {
            background-color: #274471;
            color: white;
            font-weight: bold;
        }
        
        .users-table th a {
            color: white !important;
            text-decoration: none;
            display: block;
            width: 100%;
        }
        
        .users-table th a:hover {
            color: #ffd700 !important;
            text-decoration: underline;
        }
        
        .sort-link {
            color: white !important;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px;
            transition: all 0.2s ease;
        }
        
        .sort-link:hover {
            color: #ffd700 !important;
            text-decoration: none;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sort-indicator {
            font-size: 14px;
            font-weight: bold;
            margin-left: 5px;
        }
        
        .users-table td a {
            color: #274471;
            text-decoration: none;
            font-weight: bold;
        }
        
        .users-table td a:hover {
            color: #1e3554;
            text-decoration: underline;
        }
        
        .users-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        
        .status-active {
            color: #28a745;
            font-weight: bold;
        }
        
        .status-inactive {
            color: #6c757d;
        }
        
        .type-admin {
            color: #dc3545;
            font-weight: bold;
        }
        
        .type-case-manager {
            color: #17a2b8;
            font-weight: bold;
        }
        
        .type-maintenance {
            color: #ffc107;
            font-weight: bold;
        }
        
        .type-volunteer {
            color: #28a745;
        }
        
        .profile-link {
            color: #274471;
            text-decoration: none;
            font-weight: 500;
            padding: 6px 12px;
            border-radius: 4px;
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            transition: all 0.2s ease;
            display: inline-block;
            font-size: 14px;
        }
        
        .profile-link:hover {
            background-color: #274471;
            color: white;
            text-decoration: none;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(39, 68, 113, 0.2);
        }
        
        .search-results {
            margin-top: 2rem;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        .results-table {
            margin-top: 1rem;
            overflow-x: auto;
        }
        
        .results-table table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .results-table th,
        .results-table td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        
        .results-table th {
            background: #007bff;
            color: white;
            font-weight: 600;
        }
        
        .results-table tr:hover {
            background: #f8f9fa;
        }
        
        .btn-small {
            display: inline-block;
            padding: 6px 12px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.875rem;
            transition: background-color 0.2s;
        }
        
        .btn-small:hover {
            background: #0056b3;
            color: white;
        }
    </style>
    
    <script>
        // preserve scroll position when sorting
        document.addEventListener('DOMContentLoaded', function() {
            // store scroll position before navigation
            const sortLinks = document.querySelectorAll('.sort-link');
            sortLinks.forEach(function(link) {
                link.addEventListener('click', function(e) {
                    // store current scroll position
                    sessionStorage.setItem('scrollPosition', window.pageYOffset);
                });
            });
            
            // restore scroll position after page load
            const savedScrollPosition = sessionStorage.getItem('scrollPosition');
            if (savedScrollPosition !== null) {
                window.scrollTo(0, parseInt(savedScrollPosition));
                sessionStorage.removeItem('scrollPosition');
            }
        });
    </script>
</body>
</html>