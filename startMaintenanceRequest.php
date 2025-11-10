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

// Only Staff (1), Case Managers (2), and Admin (3) can start requests.
if (!($accessLevel == 1 || $accessLevel == 2 || $accessLevel == 3)) {
    header('Location: micahportal.php');
    die();
}

require_once('database/dbMaintenanceRequests.php');
require_once('domain/MaintenanceRequest.php');

// If POST: perform the status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    $mr = get_maintenance_request_by_id($id);
    if ($mr && strtolower($mr->getStatus()) === 'pending' && !$mr->getArchived()) {
        // Set to In Progress
        $mr->setStatus('In Progress');
        update_maintenance_request($mr);
    }
    header('Location: viewAllMaintenanceRequests.php');
    die();
}

// If GET: show confirm page
$id = $_GET['id'] ?? null;
$mr = $id ? get_maintenance_request_by_id($id) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Start Maintenance Request</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .confirm-card {
      background: #fff;
      border: 1px solid #ddd;
      border-radius: 6px;
      padding: 18px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.08);
      margin: 30px auto;
      max-width: 720px;
    }
    .btn-primary {
      background-color: #007bff;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
      font-weight: 700;
    }
    .btn-danger {
      background-color: #6c757d;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
      margin-left: 8px;
    }
  </style>
</head>
<body>
  <?php include 'header.php'; ?>
  <main role="main">
    <div class="container">
      <div class="row">
        <div class="col-12">
          <?php if ($mr): ?>
            <div class="confirm-card">
              <h2 style="margin-top:0;">Start Pending Request</h2>
              <p>
                <!-- [ADDED 2025-11-09] Confirm starting a maintenance request. -->
                Are you sure you want to mark maintenance request <strong>#<?php echo htmlspecialchars($mr->getID()); ?></strong> as <strong>In Progress</strong>?
              </p>
              <form method="post" action="startMaintenanceRequest.php">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($mr->getID()); ?>">
                <button type="submit" class="btn-primary">Start Maintenance Request</button>
                <a href="viewAllMaintenanceRequests.php" class="btn-danger">Cancel</a>
              </form>
            </div>
          <?php else: ?>
            <div class="confirm-card">
              We couldn’t find that maintenance request.
              <a href="viewAllMaintenanceRequests.php" class="btn-primary" style="margin-left:8px;">Back to Requests</a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </main>
</body>
</html>
