<?php
session_cache_expire(30);
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$loggedIn    = isset($_SESSION['_id']);
$accessLevel = $loggedIn ? (int)$_SESSION['access_level'] : 0;
if ($accessLevel < 2) { header('Location: index.php'); die(); }

$requestId = isset($_GET['request_id']) ? trim($_GET['request_id']) : '';
if ($requestId === '') { header('Location: viewArchive.php'); die(); }

// TODO: Replace with real DB call/retrieval
$request = [
  'id'         => $requestId ?: '1',
  'title'      => 'Leaky faucet',
  'address'    => '123 Maple St, Unit A',
  'created'    => '2025-04-02 09:15:00',
  'closed'     => '2025-04-06 16:22:00',
  'status'     => 'Archived',
  'assigned_to'=> 'cmiller',
  'description'=> 'Kitchen sink dripping; washer likely worn.',
];


// TODO: Replace with real DB
$history = [
  ['ts'=>'2025-04-06 16:22:00','user'=>'cmiller','field'=>'status','old'=>'In Progress','new'=>'Archived'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="icon" type="image/png" href="images/micah-favicon.png">
  <title>Request History — <?= htmlspecialchars($request['id']) ?></title>
  <link href="css/normal_tw.css" rel="stylesheet">
  <?php $tailwind_mode = true; require_once('header.php'); ?>
  <style>
    .date-box{background:#274471;padding:7px 30px;border-radius:50px;box-shadow:-4px 4px 4px rgba(0,0,0,.25) inset;color:#fff;font-size:24px;font-weight:700;text-align:center}
    .dropdown{padding-right:50px}
    .section-card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px}
    .grid-2{display:grid;grid-template-columns:1.2fr .8fr;gap:24px}
    @media (max-width: 1000px){.grid-2{grid-template-columns:1fr}}
    .kv{display:grid;grid-template-columns:180px 1fr;gap:8px 16px;font-size:15px}
    .kv dt{color:#6b7280;font-weight:600}
    .kv dd{color:#111827}
    .pill{display:inline-block;padding:4px 10px;border-radius:9999px;font-weight:600;font-size:13px}
    .pill-arch{background:#f3f4f6;color:#374151;border:1px solid #e5e7eb}
    .history-list{list-style:none;margin:0;padding:0}
    .history-item{display:flex;gap:14px;align-items:flex-start;padding:12px 10px;border-bottom:1px solid #f3f4f6}
    .history-item:last-child{border-bottom:none}
    .mono{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace}
    .btn{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:8px;background:#274471;color:#fff;border:none;cursor:pointer;font-weight:600;text-decoration:none}
    .btn:hover{background:#345284}
    .top-actions{display:flex;gap:10px;justify-content:flex-end;margin-bottom:16px}
  </style>
</head>
<body>
  <div class="hero-header">
    <div class="center-header">
      <h1>Request <?= htmlspecialchars($request['id']) ?></h1>
    </div>
  </div>

  <main>
    <div class="main-content-box p-6">

      <div class="top-actions">
        <a class="btn" href="viewArchive.php">← Back to Archived Requests</a>
        <a class="btn" href="micahportal.php">Dashboard</a>
      </div>

      <!-- Current request details -->
      <section class="section-card" style="margin-bottom:20px;">
        <h2 class="text-2xl font-bold mb-2">Current Details</h2>
        <dl class="kv">
          <dt>Title</dt><dd><?= htmlspecialchars($request['title']) ?></dd>
          <dt>Address</dt><dd><?= htmlspecialchars($request['address']) ?></dd>
          <dt>Created</dt><dd><?= htmlspecialchars($request['created']) ?></dd>
          <dt>Closed</dt><dd><?= htmlspecialchars($request['closed']) ?></dd>
          <dt>Status</dt><dd><span class="pill pill-arch"><?= htmlspecialchars($request['status']) ?></span></dd>
          <dt>Assigned To</dt><dd><?= htmlspecialchars($request['assigned_to']) ?></dd>
          <dt>Description</dt><dd><?= htmlspecialchars($request['description']) ?></dd>
        </dl>
      </section>

      <!-- Change history -->
      <section class="section-card">
        <h2 class="text-xl font-bold mb-2">Change History</h2>
        <ul class="history-list">
          <?php if (empty($history)): ?>
            <li class="history-item"><em>No history found for this request.</em></li>
          <?php else: foreach ($history as $h): ?>
            <li class="history-item">
              <div style="min-width:180px">
                <div class="mono" style="color:#374151;font-weight:700;"><?= htmlspecialchars($h['ts']) ?></div>
                <div class="mono" style="color:#6b7280;">by <?= htmlspecialchars($h['user']) ?></div>
              </div>
              <div class="mono">
                <strong><?= htmlspecialchars($h['field']) ?></strong>:
                <span style="color:#6b7280">from</span> <?= htmlspecialchars($h['old']) ?>
                <span style="color:#6b7280">to</span> <?= htmlspecialchars($h['new']) ?>
              </div>
            </li>
          <?php endforeach; endif; ?>
        </ul>
      </section>

      <div class="mt-10 flex justify-end">
        <a href="micahportal.php" class="blue-button">Return to Dashboard</a>
      </div>
    </div>
  </main>
</body>
</html>
