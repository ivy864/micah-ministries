<?php
session_cache_expire(30);
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$loggedIn    = isset($_SESSION['_id']);
$accessLevel = $loggedIn ? (int)$_SESSION['access_level'] : 0;
if ($accessLevel < 2) { header('Location: index.php'); die(); }

// TODO: Replace example with DB call 
$requests = [
  [
    'id'       => '1',
    'title'    => 'Leaky faucet',
    'address'  => '123 Maple St, Unit A',
    'status'   => 'Archived',
    'created'  => '2025-04-02 09:15:00',
    'closed'   => '2025-04-06 16:22:00',
  ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="icon" type="image/png" href="images/micah-favicon.png">
  <title>Micah Ministries | View Archived Requests</title>
  <link href="css/normal_tw.css" rel="stylesheet">
  <?php $tailwind_mode = true; require_once('header.php'); ?>
  <style>
    .date-box {
      background: #274471;
      padding: 7px 30px;
      border-radius: 50px;
      box-shadow: -4px 4px 4px rgba(0, 0, 0, 0.25) inset;
      color: #fff;
      font-size: 24px;
      font-weight: 700;
      text-align: center;
    }
    .dropdown { padding-right: 50px; }
    .pill {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 9999px;
      font-weight: 600;
      font-size: 13px;
    }
    .pill-arch {
      background: #f3f4f6;
      color: #374151;
      border: 1px solid #e5e7eb;
    }
    .actions {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
    }
  </style>
</head>
<body>
  <div class="hero-header">
    <div class="center-header">
      <h1>View Archived Requests</h1>
    </div>
  </div>

  <main>
    <div class="main-content-box p-6">
      <div class="overflow-x-auto">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Title</th>
              <th>Address</th>
              <th>Closed</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($requests)): ?>
            <tr>
              <td colspan="6" style="text-align:center; color:#6b7280; padding:12px;">
                No archived requests found.
              </td>
            </tr>
          <?php else: foreach ($requests as $r): ?>
            <tr>
              <td><?= htmlspecialchars($r['id']) ?></td>
              <td><?= htmlspecialchars($r['title']) ?></td>
              <td><?= htmlspecialchars($r['address']) ?></td>
              <td><?= htmlspecialchars($r['closed']) ?></td>
              <td><span class="pill pill-arch"><?= htmlspecialchars($r['status']) ?></span></td>
              <td class="actions">
                <a href="requestHistory.php?request_id=<?= urlencode($r['id']) ?>" class="return-button">View History</a>
              </td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>

      <div class="mt-10 flex justify-end">
        <a href="micahportal.php" class="blue-button">Return to Dashboard</a>
      </div>
    </div>
  </main>
</body>
</html>
