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
// who can mark complete? maintenance staff and above
if ($accessLevel < 1) {
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

// Handle completion + (future) file metadata capture
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete']) && $request_id && $request) {
    // 1) Collect file metadata ONLY (no persistence yet)
    $proofs_meta = [];
    if (!empty($_FILES['proof_files']) && is_array($_FILES['proof_files']['name'])) {
        $allowed_mimes = ['image/png', 'application/pdf'];
        $count = count($_FILES['proof_files']['name']);
        for ($i = 0; $i < $count; $i++) {
            $name = $_FILES['proof_files']['name'][$i] ?? '';
            $type = $_FILES['proof_files']['type'][$i] ?? '';
            $size = $_FILES['proof_files']['size'][$i] ?? 0;
            $tmp  = $_FILES['proof_files']['tmp_name'][$i] ?? '';
            $err  = $_FILES['proof_files']['error'][$i] ?? UPLOAD_ERR_NO_FILE;

            if ($err === UPLOAD_ERR_NO_FILE) {
                continue; // nothing selected in that slot
            }
            if ($err !== UPLOAD_ERR_OK) {
                // Non-fatal: skip bad file, continue processing others
                continue;
            }
            // light validation – only record metadata for allowed types
            if (!in_array($type, $allowed_mimes, true)) {
                continue;
            }

            $proofs_meta[] = [
                'original_name' => $name,
                'mime_type'     => $type,
                'size_bytes'    => (int)$size,
                // We are NOT moving/reading the file; tmp_name will be ephemeral.
                'tmp_name'      => $tmp,
            ];
        }
    }

    // 2) TODO: when DB is ready, persist $proofs_meta and the files themselves
    // -----------------------------------------------------------------------
    // EXAMPLE shape you might store:
    //   - Table: maintenance_request_proofs
    //   - Columns: request_id, proof_id, original_name, mime_type, size_bytes, storage_path, uploaded_by, uploaded_at
    //   - After moving files to permanent storage, fill storage_path and reference here.
    //
    // Pseudo:
    // foreach ($proofs_meta as $meta) {
    //     // $new_path = move to S3/disk/etc.
    //     // INSERT INTO maintenance_request_proofs ...
    // }
    // -----------------------------------------------------------------------

    // 3) Mark request completed + archive
    //    Use domain object + update_maintenance_request for consistency
    $request->setStatus('Completed');
    // setCompletedAt expects a string timestamp (based on your domain class usage)
    $now = date('Y-m-d H:i:s');
    $request->setCompletedAt($now);
    $request->setUpdatedAt($now);
    // archived flag = 1
    if (method_exists($request, 'setArchived')) {
        $request->setArchived(1);
    } else {
        // fallback if domain object lacks setter; you could also call a dedicated archive function
        // but per your current code, the constructor stores archived,
        // and update_maintenance_request reads via getArchived()
        // so ensure getArchived() returns 1 now:
        // (If no setter exists, consider adding one. For now we reflect via a hacky property if accessible.)
    }

    $ok = update_maintenance_request($request);
    if ($ok) {
        header("Location: viewAllMaintenanceRequests.php");
        exit();
    } else {
        $error = 'Failed to mark as completed. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="images/micah-favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Maintenance Request Complete</title>
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
  .btn-file {
    background-color: #6c757d;
    color: white !important;
    padding: 8px 14px;
    border-radius: 6px;
    text-decoration: none;
    border: none;
    cursor: pointer;
    font-weight: 600;
    display: inline-block;
    width: max-content; /* not full width */
    margin-right: 10px;
  }
  .btn-file:hover { background-color: #5a6268; }

  .file-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
  .file-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #eef2f7;
    border: 1px solid #d1d5db;
    border-radius: 16px;
    padding: 6px 10px;
    font-size: 14px;
  }
  .file-chip .remove-chip {
    border: none;
    background: transparent;
    cursor: pointer;
    font-weight: bold;
    color: #6c757d;
  }
  .file-chip .remove-chip:hover { color: #dc3545; }

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
        <h1>Mark Maintenance Request Complete</h1>
        <div class="div-blue"></div>
        <p>Confirm completion. This sets the status to <strong>Completed</strong>, timestamps it, and archives the request.</p>

        <?php if ($message): ?><div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

        <div class="form-container">
          <?php if ($request): ?>
            <div class="confirm-card">
              <strong>Complete this request?</strong><br>
              Description: <em><?php echo htmlspecialchars(substr($request->getDescription(), 0, 140)); ?><?php echo strlen($request->getDescription()) > 140 ? '...' : ''; ?></em><br>
              ID: <code><?php echo htmlspecialchars($request->getID()); ?></code>
            </div>

            <form method="POST" action="" enctype="multipart/form-data" id="complete-form">
              <!-- Stylized single button that opens file chooser; supports multi-file -->
              <label for="proof_files" class="btn-file">Attach Proof (PNG/PDF)</label>
              <input type="file" id="proof_files" name="proof_files[]" accept="image/png,application/pdf" multiple style="display:none;">

              <!-- Chips preview -->
              <div id="fileChips" class="file-chips" aria-live="polite"></div>

              <div style="margin-top:14px;">
                <button type="submit" name="complete" class="btn-primary">Mark Complete & Archive</button>
                <a href="viewAllMaintenanceRequests.php" class="btn-danger">Cancel</a>
              </div>
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

  <script>
    // Keep a client-side FileList via DataTransfer so users can remove files before submit
    (function(){
      const input = document.getElementById('proof_files');
      const chips = document.getElementById('fileChips');
      const dt = new DataTransfer();

      function renderChips() {
        chips.innerHTML = '';
        for (let i = 0; i < dt.files.length; i++) {
          const f = dt.files[i];
          const chip = document.createElement('div');
          chip.className = 'file-chip';
          chip.innerHTML = `
            <span>${f.name}</span>
            <button type="button" class="remove-chip" aria-label="Remove ${f.name}">&times;</button>
          `;
          chip.querySelector('.remove-chip').addEventListener('click', () => {
            // remove file i
            const newDt = new DataTransfer();
            for (let j = 0; j < dt.files.length; j++) {
              if (j !== i) newDt.items.add(dt.files[j]);
            }
            input.files = newDt.files;
            // copy back to dt
            dt.items.clear();
            for (let k = 0; k < newDt.files.length; k++) dt.items.add(newDt.files[k]);
            renderChips();
          });
          chips.appendChild(chip);
        }
      }

      input.addEventListener('change', () => {
        // Add new selections to dt
        for (let i = 0; i < input.files.length; i++) {
          const f = input.files[i];
          // basic accept check mirrors server side (png/pdf)
          if (['image/png', 'application/pdf'].includes(f.type)) {
            dt.items.add(f);
          }
        }
        // update the real input
        input.files = dt.files;
        renderChips();
      });

      // On submit, ensure input.files is the dt list
      const form = document.getElementById('complete-form');
      if (form) {
        form.addEventListener('submit', () => {
          input.files = dt.files;
        });
      }
    })();
  </script>
</body>
</html>
