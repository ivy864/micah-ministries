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
    header('Location: micahportal.php');
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

if (!function_exists('restore_maintenance_request')) {
    function restore_maintenance_request($id) {
        $req = get_maintenance_request_by_id($id);
        if (!$req) return false;

        $con = connect();
        $id_esc = mysqli_real_escape_string($con, $id);

        // UPDATED QUERY — restores archive flag AND resets status
        $qry = "
            UPDATE dbmaintenancerequests
            SET archived = 0,
                status = 'Pending'
            WHERE id = '$id_esc'
        ";

        $ok = mysqli_query($con, $qry);
        mysqli_close($con);

        return (bool)$ok;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restore']) && $request_id) {
    if (restore_maintenance_request($request_id)) {

        // UPDATED REDIRECT — sends user back to ACTIVE requests
        header("Location: viewAllMaintenanceRequests.php");
        exit();

    } else {
        $error = 'Failed to restore the maintenance request. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="images/micah-favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restore Maintenance Request</title>
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
        <h1 class="main-text">Restore Maintenance Request</h1>
        <p class="secondary-text">Confirm restoration of this maintenance request.</p>

        <?php if ($message): ?><div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

        <div class="form-container">
          <?php if ($request): ?>
            <div class="confirm-card">
              <strong>Are you sure you want to restore this request?</strong><br>
              Description: <em><?php echo htmlspecialchars(substr($request->getDescription(), 0, 120)); ?><?php echo strlen($request->getDescription()) > 120 ? '...' : ''; ?></em><br>
              ID: <code><?php echo htmlspecialchars($request->getID()); ?></code>
            </div>

            <form method="POST" action="">
              <button type="submit" name="restore" class="blue-button">Restore Maintenance Request</button>
              <a href="viewAllMaintenanceRequests.php?archived=1" class="delete-button">Cancel</a>
            </form>
          <?php else: ?>
            <div class="confirm-card">
              We couldn’t find that maintenance request. 
              <a href="viewAllMaintenanceRequests.php?archived=1" class="blue-button" style="margin-left:8px;">
                Back to Archived List
              </a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </main>
</body>
</html>
