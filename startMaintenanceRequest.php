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

// only staff (1), case managers (2), and admin (3) can start requests
if (!($accessLevel == 1 || $accessLevel == 2 || $accessLevel == 3)) {
    header('Location: index.php');
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
    <link rel="icon" type="image/png" href="images/micah-favicon.png">
    <meta charset="UTF-8">
    <title>Start Maintenance Request</title>
    <link href="css/base.css?v=<?php echo time(); ?>" rel="stylesheet">

<?php
$tailwind_mode = true;
require_once('header.php');
?>
</head>

<body>
  <main>
    <div class="sections">
      <div class="button-section"></div>

      <div class="text-section">
        <h1 class="main-text">Start Maintenance Request</h1>
        <p class="secondary-text">Confirm that you want to mark this request as In Progress.</p>

        <div class="form-container">
          <?php if ($mr): ?>
            <div class="confirm-card">
              <strong>Are you sure you want to start this maintenance request?</strong><br>
              ID: <code><?php echo htmlspecialchars($mr->getID()); ?></code><br>
              Status will be updated to: <strong>In Progress</strong>
            </div>

            <form method="POST" action="">
              <input type="hidden" name="id" value="<?php echo htmlspecialchars($mr->getID()); ?>">
              <button type="submit" class="blue-button">Start Maintenance Request</button>
              <a href="viewAllMaintenanceRequests.php" class="delete-button" style="margin-left:6px;">Cancel</a>
            </form>

          <?php else: ?>
            <div class="confirm-card">
              We couldn’t find that maintenance request.
              <a href="viewAllMaintenanceRequests.php" class="blue-button" style="margin-left:8px;">Back to Requests</a>
            </div>
          <?php endif; ?>
        </div>

      </div>
    </div>
  </main>
</body>
</html>
`
