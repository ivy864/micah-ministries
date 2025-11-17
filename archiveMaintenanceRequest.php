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
if ($accessLevel < 2) {
    header('Location: index.php');
    die();
}

require_once('database/dbMaintenanceRequests.php');
require_once('domain/MaintenanceRequest.php');

$message = '';
$error = '';

$request_id = $_GET['id'] ?? null;
$request = null;
if ($request_id) {
    $request = get_maintenance_request_by_id($request_id);
    if (!$request) $error = 'Maintenance request not found.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['archive']) && $request_id) {
    if (delete_maintenance_request($request_id)) {
        header("Location: viewAllMaintenanceRequests.php");
        exit();
    } else $error = 'Failed to archive the maintenance request. Please try again.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="images/micah-favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archive Maintenance Request</title>
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
        <h1 class="main-text">Archive Maintenance Request</h1>
        <p class="secondary-text">Confirm archival of this maintenance request.</p>

        <?php if ($message): ?><div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

        <div class="form-container">
          <?php if ($request): ?>
            <div class="confirm-card">
              <strong>Are you sure you want to archive this request?</strong><br>
              Description: <em><?php echo htmlspecialchars(substr($request->getDescription(), 0, 120)); ?><?php echo strlen($request->getDescription()) > 120 ? '...' : ''; ?></em><br>
              ID: <code><?php echo htmlspecialchars($request->getID()); ?></code>
            </div>

            <form method="POST" action="">
              <button type="submit" name="archive" class="blue-button">Archive Maintenance Request</button>
              <a href="viewAllMaintenanceRequests.php" class="delete-button">Cancel</a>
            </form>
          <?php else: ?>
            <div class="confirm-card">
              We couldn’t find that maintenance request. <a href="viewAllMaintenanceRequests.php" class="blue-button" style="margin-left:8px;">Back to List</a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </main>
</body>
</html>
