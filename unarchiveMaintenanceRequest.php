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

if (!function_exists('unarchive_maintenance_request')) {
    function unarchive_maintenance_request($id) {
        $req = get_maintenance_request_by_id($id);
        if (!$req) return false;
        $con = connect();
        $qry = "UPDATE dbmaintenancerequests SET archived = 0 WHERE id = '" . mysqli_real_escape_string($con, $id) . "'";
        $ok = mysqli_query($con, $qry);
        mysqli_close($con);
        return (bool)$ok;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unarchive']) && $request_id) {
    if (unarchive_maintenance_request($request_id)) {
        header("Location: viewAllMaintenanceRequests.php?archived=1");
        exit();
    } else $error = 'Failed to unarchive the maintenance request. Please try again.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="images/micah-favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unarchive Maintenance Request</title>
    <link href="css/management_tw.css?v=<?php echo time(); ?>" rel="stylesheet">
<?php
$tailwind_mode = true;
require_once('header.php');
?>
<style>
  .confirm-card {
    background-color: #fff3cd;
    color: #856404;
    border: 1px solid #ffeeba;
    border-radius: 6px;
    padding: 16px;
    margin-bottom: 16px;
  }
  .btn-primary {
    background-color: #274471;
    color: white !important;
    padding: 10px 16px;
    border-radius: 6px;
    text-decoration: none;
    border: none;
    cursor: pointer;
    font-weight: 600;
    display: inline-block;
  }
  .btn-primary:hover { background-color: #1e3554; }
  .btn-danger {
    background-color: #dc3545;
    color: white !important;
    padding: 10px 16px;
    border-radius: 6px;
    text-decoration: none;
    border: none;
    cursor: pointer;
    font-weight: 600;
    display: inline-block;
    margin-left: 10px;
  }
  .btn-danger:hover { background-color: #b02a37; }
  .form-container {
    max-width: 720px;
    width: 100%;
    margin: 10px auto;
    padding: 16px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.10);
    border: 2px solid #274471;
  }
  .alert {
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 16px;
  }
  .alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
  }
  .alert-error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
  }
  .sections { flex-direction: row !important; gap: 10px !important; }
  main { margin-top: 0 !important; padding: 10px !important; }
  .button-section { width: 0% !important; display: none !important; }
  .text-section { width: 100% !important; }
  .text-section h1 { margin-bottom: 6px !important; }
  .text-section p { margin-bottom: 10px !important; }
</style>
</head>
<body>
  <main>
    <div class="sections">
      <div class="button-section"></div>
      <div class="text-section">
        <h1>Unarchive Maintenance Request</h1>
        <div class="div-blue"></div>
        <p>Confirm unarchiving of this maintenance request.</p>

        <?php if ($message): ?><div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

        <div class="form-container">
          <?php if ($request): ?>
            <div class="confirm-card">
              <strong>Are you sure you want to unarchive this request?</strong><br>
              Description: <em><?php echo htmlspecialchars(substr($request->getDescription(), 0, 120)); ?><?php echo strlen($request->getDescription()) > 120 ? '...' : ''; ?></em><br>
              ID: <code><?php echo htmlspecialchars($request->getID()); ?></code>
            </div>

            <form method="POST" action="">
              <button type="submit" name="unarchive" class="btn-primary">Unarchive Maintenance Request</button>
              <a href="viewAllMaintenanceRequests.php?archived=1" class="btn-danger">Cancel</a>
            </form>
          <?php else: ?>
            <div class="confirm-card">
              We couldn’t find that maintenance request. <a href="viewAllMaintenanceRequests.php?archived=1" class="btn-primary" style="margin-left:8px;">Back to Archived List</a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </main>
</body>
</html>
