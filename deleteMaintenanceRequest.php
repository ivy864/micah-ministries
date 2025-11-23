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

/**
 * Deleting should be admin/manager only.
 * If you want maintenance staff to delete, lower this threshold to >=1.
 */
if ($accessLevel < 2) {
    header('Location: micahportal.php');
    die();
}

require_once('database/dbMaintenanceRequests.php');
require_once('domain/MaintenanceRequest.php');

$message    = '';
$error      = '';
$request_id = $_GET['id'] ?? null;
$request    = null;
$is_archived = false;

if ($request_id) {
    $request = get_maintenance_request_by_id($request_id);
    if (!$request) {
        $error = 'Maintenance request not found.';
    } else {
        $is_archived = ((int)$request->getArchived() === 1);
    }
} else {
    $error = 'No maintenance request ID provided.';
}

/**
 * Hard delete helper (remove row). Uses the same connection helpers already loaded.
 */
function hard_delete_maintenance_request($id) {
    $con = connect();
    $query = "DELETE FROM dbmaintenancerequests WHERE id = ?";
    $stmt = mysqli_prepare($con, $query);

    if (!$stmt) {
        $err = mysqli_error($con);
        mysqli_close($con);
        return [false, $err];
    }

    mysqli_stmt_bind_param($stmt, "s", $id);
    $result = mysqli_stmt_execute($stmt);
    $error = $result ? null : mysqli_error($con);

    mysqli_stmt_close($stmt);
    mysqli_close($con);
    return [$result, $error];
}

// Handle POST confirm
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete']) && $request && !$error) {

    if ($is_archived) {
        // Already archived → HARD DELETE
        list($ok, $dbErr) = hard_delete_maintenance_request($request_id);
        if ($ok) {
            // It no longer exists; send back to archived list
            header('Location: viewAllMaintenanceRequests.php?archived=1');
            exit();
        } else {
            $error = 'Failed to permanently delete the maintenance request. ' . ($dbErr ? '(' . $dbErr . ')' : '');
        }
    } else {
        // Active → SOFT DELETE (archive + status = Deleted)
        $request->setStatus('Deleted');
        $request->setArchived(1);
        $request->setUpdatedAt(date('Y-m-d H:i:s'));

        if (update_maintenance_request($request)) {
            // Now appears under archived
            header('Location: viewAllMaintenanceRequests.php?archived=1');
            exit();
        } else {
            $error = 'Failed to delete (soft delete) the maintenance request. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="images/micah-favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_archived ? 'Permanently Delete' : 'Delete'; ?> Maintenance Request</title>
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
    margin-right: 10px;
  }
  .btn-danger:hover { background-color: #b02a37; }

  .btn-secondary {
    background-color: #6c757d;
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
  .btn-secondary:hover { background-color: #5a6268; }

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
        <h1><?php echo $is_archived ? 'Permanently Delete' : 'Delete'; ?> Maintenance Request</h1>
        <div class="div-blue"></div>
        <p>
          <?php if ($is_archived): ?>
            This request is already archived. Deleting now will <strong>permanently remove it from the database</strong>.
          <?php else: ?>
            Deleting this active request will <strong>move it to Archived</strong> and set its status to <em>Deleted</em>.
          <?php endif; ?>
        </p>

        <?php if ($message): ?>
          <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
          <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="form-container">
          <?php if ($request && !$error): ?>
            <div class="confirm-card">
              <strong>
                <?php echo $is_archived ? 'Permanently delete this request?' : 'Delete this request?'; ?>
              </strong><br>
              Description:
              <em>
                <?php
                  $desc = $request->getDescription();
                  echo htmlspecialchars(mb_strimwidth($desc, 0, 120, (strlen($desc) > 120 ? '...' : '')));
                ?>
              </em><br>
              ID: <code><?php echo htmlspecialchars($request->getID()); ?></code>
            </div>

            <form method="POST" action="">
              <button type="submit" name="confirm_delete" class="btn-danger">
                <?php echo $is_archived ? 'Permanently Delete' : 'Delete (Move to Archived)'; ?>
              </button>
              <a href="viewAllMaintenanceRequests.php<?php echo $is_archived ? '?archived=1' : ''; ?>" class="btn-secondary">Cancel</a>
            </form>
          <?php else: ?>
            <div class="confirm-card">
              We couldn’t find that maintenance request.
              <a href="viewAllMaintenanceRequests.php" class="btn-primary" style="margin-left:8px;">Back to List</a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </main>
</body>
</html>
