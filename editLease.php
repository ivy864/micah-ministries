<?php
session_cache_expire(30);
session_start();

$loggedIn = false;
$accessLevel = 0;
$userID = null;

if (isset($_SESSION['_id'])) {
    $loggedIn = true;
    $accessLevel = $_SESSION['access_level'];
    $userID = $_SESSION['_id'];
}

require_once('domain/Comment.php');
require_once('database/dbComments.php');

if ($accessLevel < 2) {
    header('Location: index.php');
    die();
}
$lease_id = $_GET['id'] ?? null;

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
            $result = add_lease_comment($cmnt);
            
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

$pdo = null;
$db_notice = null;
$lease = [
    'tenant_name' => '',
    'property_address' => '',
    'unit_number' => '',
    'start_date' => '',
    'expiration_date' => '',
    'monthly_rent' => '',
    'security_deposit' => ''
];

try {
    $conf = null;
    $confPath = __DIR__ . '/database/dbViewLease.php.'; // change to actual db

    if (file_exists($confPath)) {
        include $confPath; // sets $conf
    }

    $dsn  = $conf['dsn']  ?? getenv('MICAH_DB_DSN');
    $user = $conf['user'] ?? getenv('MICAH_DB_USER');
    $pass = $conf['pass'] ?? getenv('MICAH_DB_PASS');

    if ($dsn) {
        $pdo = new PDO($dsn, $user ?: null, $pass ?: null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } else {
        $db_notice = "Database not yet configured — please fill in database/dbViewLease.php."; // change to actual db
    }
} catch (Throwable $e) {
    $db_notice = "Database connection failed: " . htmlspecialchars($e->getMessage());
}



if ($pdo && $lease_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT tenant_name, property_address, unit_number, start_date, 
                   expiration_date, monthly_rent, security_deposit 
            FROM leases 
            WHERE id = :id 
            LIMIT 1
        ");
        $stmt->execute([':id' => $lease_id]);
        if ($row = $stmt->fetch()) {
            $lease = array_merge($lease, $row);
        } else {
            $db_notice = "No lease found for ID " . htmlspecialchars($lease_id);
        }
    } catch (Throwable $e) {
        $db_notice = "Error loading lease: " . htmlspecialchars($e->getMessage());
    }
}

if ($pdo && $_SERVER['REQUEST_METHOD'] === 'POST' && $lease_id) {
    $fields = [
        'tenant_name',
        'property_address',
        'unit_number',
        'start_date',
        'expiration_date',
        'monthly_rent',
        'security_deposit',
        'program_type',
        'status',
        'notes'
    ];
    $data = [];

    foreach ($fields as $f) {
        if (isset($_POST[$f])) {
            $data[$f] = $_POST[$f];
        }
    }

    if (!empty($data)) {
        $sets = [];
        $params = [':id' => $lease_id];

        foreach ($data as $k => $v) {
            $sets[] = "$k = :$k";
            $params[":$k"] = $v;
        }

        try {
            $sql = "UPDATE leases SET " . implode(", ", $sets) . " WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $lease = array_merge($lease, $data);
            $db_notice = "✅ Lease updated successfully.";
        } catch (Throwable $e) {
            $db_notice = "❌ Update failed: " . htmlspecialchars($e->getMessage());
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>     <link rel="icon" type="image/png" href="images/micah-favicon.png">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Micah Ministries | Edit Lease</title>
    <link href="css/base.css?v=<?php echo time(); ?>" rel="stylesheet">
    <script src="js/comment.js?v=<?php echo time(); ?>"></script>
    <?php
    $tailwind_mode = true;
    require_once('header.php');
    ?>

    <style>
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
</head>

<body>
    <div class="hero-header">
        <div class="center-header">
            <h1>Edit a Lease</h1>
        </div>
    </div>

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
        <h1 class="main-text">Edit Lease</h1>
        <div class="div-blue"></div>
        <p class="secondary-text">
          Update existing lease information. Modify the fields below as needed and submit your changes to save the updated lease details.
        </p>
        
        <?php if (isset($db_notice) && $db_notice): ?>
            <div class="alert <?php echo ($pdo ? 'alert-success' : 'alert-error'); ?>">
                <?php echo $db_notice; ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form action="" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="tenant_first_name">Tenant First Name:</label>
                        <input type="text" id="tenant_first_name" name="tenant_first_name"
                            value="<?php echo htmlspecialchars($lease['tenant_first_name']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="tenant_last_name">Tenant Last Name:</label>
                        <input type="text" id="tenant_last_name" name="tenant_last_name"
                            value="<?php echo htmlspecialchars($lease['tenant_last_name']); ?>">
                    </div>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-4">
                <div>
                    <label for="tenant_name" class="block font-medium text-gray-700">Tenant Name:</label>
                    <input type="text" id="tenant_name" name="tenant_name"
                        value="<?php echo htmlspecialchars($lease['tenant_name']); ?>"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                </div>

                <div>
                    <label for="property_address" class="block font-medium text-gray-700">Property Address:</label>
                    <input type="text" id="property_address" name="property_address"
                        value="<?php echo htmlspecialchars($lease['property_address']); ?>"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                </div>

                <div>
                    <label for="unit_number" class="block font-medium text-gray-700">Unit Number:</label>
                    <input type="text" id="unit_number" name="unit_number"
                        value="<?php echo htmlspecialchars($lease['unit_number']); ?>"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                </div>

                <div>
                    <label for="start_date" class="block font-medium text-gray-700">Lease Start Date:</label>
                    <input type="date" id="start_date" name="start_date"
                        value="<?php echo htmlspecialchars($lease['start_date']); ?>"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                </div>

                <div>
                    <label for="expiration_date" class="block font-medium text-gray-700">Lease End Date:</label>
                    <input type="date" id="expiration_date" name="expiration_date"
                        value="<?php echo htmlspecialchars($lease['expiration_date']); ?>"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                </div>

                <div>
                    <label for="monthly_rent" class="block font-medium text-gray-700">Rent Amount:</label>
                    <input type="number" step="0.01" id="monthly_rent" name="monthly_rent"
                        value="<?php echo htmlspecialchars($lease['monthly_rent']); ?>"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                </div>

                <div style="margin-top: 30px;">
                    <button type="submit" class="blue-button">Submit Changes</button>
                </div>
                
                <div style="margin-top: 20px; text-align: center;">
                    <a href="index.php" class="gray-button">Return to Dashboard</a>
                </div>
            </form>

            <div class="mt-10 flex justify-end">
                <a href="micahportal.php" class="blue-button">Return to Dashboard</a>
            </div>
        </div>

        <div id="comments" requestID="<?php echo htmlspecialchars($lease_id) ?>">
            <?php
            $comments = get_lease_comments($lease_id);
            ?>
            <h2>Comments</h2>
            <script>
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