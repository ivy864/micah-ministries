<?php

session_cache_expire(30);
session_start();
$loggedIn = isset($_SESSION['_id']);
$accessLevel = $_SESSION['access_level'] ?? 0;
if ($accessLevel < 2) { header('Location: index.php'); exit; }

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// Placeholder table
// Give each row a stable fake ID so edit/archive actions have a target
$seedRows = [
  ['id'=>101,'name'=>'Leaky faucet','date'=>'2025-10-05','type'=>'Plumbing','address'=>'101 Maple St, Apt 2B','area'=>'Kitchen'],
  ['id'=>102,'name'=>'AC not cooling','date'=>'2025-09-28','type'=>'HVAC','address'=>'44 River Rd, Unit 10','area'=>'Living Room'],
  ['id'=>103,'name'=>'Outlet sparks','date'=>'2025-10-14','type'=>'Electrical','address'=>'12 Cedar Ln, Apt 3A','area'=>'Bedroom'],
  ['id'=>104,'name'=>'Broken window latch','date'=>'2025-10-07','type'=>'Carpentry','address'=>'88 Park Ave, #5','area'=>'Bedroom'],
  ['id'=>105,'name'=>'Roach activity','date'=>'2025-09-30','type'=>'Pest Control','address'=>'7 Birch Ct, Apt 1C','area'=>'Kitchen'],
  ['id'=>106,'name'=>'Washer error code','date'=>'2025-10-11','type'=>'Appliance','address'=>'230 Oak Dr, Unit 12','area'=>'Laundry'],
  ['id'=>107,'name'=>'Toilet runs','date'=>'2025-10-02','type'=>'Plumbing','address'=>'15 Laurel St, Apt 2A','area'=>'Bathroom'],
  ['id'=>108,'name'=>'Flickering hallway light','date'=>'2025-10-09','type'=>'Electrical','address'=>'300 Pine St, #B','area'=>'Common Area'],
  ['id'=>109,'name'=>'Heater rattling','date'=>'2025-10-01','type'=>'HVAC','address'=>'19 Elm St, Apt 4C','area'=>'Living Room'],
  ['id'=>110,'name'=>'Loose handrail','date'=>'2025-10-12','type'=>'Carpentry','address'=>'55 Grove Pl, Unit 6','area'=>'Stairwell'],
  ['id'=>111,'name'=>'Ants near entry','date'=>'2025-10-03','type'=>'Pest Control','address'=>'73 Walnut St, Apt 1A','area'=>'Entryway'],
  ['id'=>112,'name'=>'Dishwasher leak','date'=>'2025-10-10','type'=>'Appliance','address'=>'410 Lakeview Rd, #2','area'=>'Kitchen'],
  ['id'=>113,'name'=>'Clogged tub drain','date'=>'2025-09-27','type'=>'Plumbing','address'=>'90 Sunrise Blvd, Apt 7D','area'=>'Bathroom'],
  ['id'=>114,'name'=>'Breaker trips','date'=>'2025-10-06','type'=>'Electrical','address'=>'142 Meadow Cir, Unit 9','area'=>'Kitchen'],
  ['id'=>115,'name'=>'Thermostat unresponsive','date'=>'2025-10-04','type'=>'HVAC','address'=>'22 Willow Way, Apt 5B','area'=>'Hallway'],
  ['id'=>116,'name'=>'Door won’t close flush','date'=>'2025-09-29','type'=>'Carpentry','address'=>'801 Ridge Rd, #3','area'=>'Bedroom'],
  ['id'=>117,'name'=>'Fruit flies','date'=>'2025-10-08','type'=>'Pest Control','address'=>'61 Brook St, Apt 2D','area'=>'Kitchen'],
  ['id'=>118,'name'=>'Oven not heating','date'=>'2025-10-13','type'=>'Appliance','address'=>'33 Maple St, Apt 4B','area'=>'Kitchen'],
  ['id'=>119,'name'=>'Low water pressure','date'=>'2025-10-15','type'=>'Plumbing','address'=>'5 Elm Ct, Apt 1B','area'=>'Bathroom'],
  ['id'=>120,'name'=>'Ceiling light hum','date'=>'2025-10-05','type'=>'Electrical','address'=>'200 Harbor Dr, Unit 14','area'=>'Living Room'],
];

// Persist the working set in-session so “Archive” can remove rows for this session only
if (!isset($_SESSION['mm_rows'])) {
  $_SESSION['mm_rows'] = $seedRows;
}
$rows = $_SESSION['mm_rows'];

// ---- Handle Archive (no DB: session-only) ----
if (isset($_GET['archive'])) {
  $archiveId = (int)$_GET['archive'];
  $rows = array_values(array_filter($rows, fn($r) => (int)$r['id'] !== $archiveId));
  $_SESSION['mm_rows'] = $rows;
  // Redirect to clean the URL
  $qs = $_GET; unset($qs['archive']);
  header('Location: ?'.http_build_query($qs));
  exit;
}

// ---- Sorting (Date, Type, Address only) ----
$allowedSort = ['date','type','address'];
$sort = $_GET['sort'] ?? 'date';
$dir  = (($_GET['dir'] ?? 'desc') === 'asc') ? 'asc' : 'desc';
if (!in_array($sort, $allowedSort, true)) { $sort = 'date'; }

usort($rows, function($a, $b) use ($sort, $dir) {
  $va = $a[$sort]; $vb = $b[$sort];
  if ($sort === 'date') { $va = strtotime($va); $vb = strtotime($vb); }
  if ($va == $vb) return 0;
  $cmp = ($va < $vb) ? -1 : 1;
  return $dir === 'asc' ? $cmp : -$cmp;
});

function sortLink($label, $col, $cur, $dir){
  $next = ($cur === $col && $dir === 'asc') ? 'desc' : 'asc';
  $qs = $_GET; $qs['sort'] = $col; $qs['dir'] = $next;
  $url = '?' . http_build_query($qs);
  $arrow = ($cur === $col) ? ($dir === 'asc' ? '▲' : '▼') : '';
  return '<a href="'.h($url).'">'.h($label)." $arrow</a>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Micah Ministries | Maintenance Management</title>
  <link href="css/normal_tw.css" rel="stylesheet">
  <style>
    /* Cap table height and allow scroll so page doesn’t stretch forever */
    .table-scroll { max-height: 60vh; overflow-y: auto; border: 1px solid #e5e7eb; border-radius: 12px; }
    table { width: 100%; border-collapse: collapse; background: #fff; }
    thead th { position: sticky; top: 0; background: #f9fafb; }
    th, td { padding: 12px 14px; border-bottom: 1px solid #f1f5f9; text-align: left; }
    .actions { display: flex; gap: 8px; }
    .btn { padding: 8px 12px; border-radius: 8px; text-decoration: none; display: inline-block; border: 1px solid #1f2937; }
    .btn.primary { background: #2563eb; border-color:#2563eb; color: #fff; }
    .btn.gray { background: #1f2937; color:#fff; }
    .btn.warn { background: #dc2626; border-color:#dc2626; color:#fff; }
    .page-actions { display:flex; gap:8px; align-items:center; }
    footer.site-footer { margin-top: 24px; padding: 16px 0; color:#6b7280; border-top:1px solid #e5e7eb; font-size: 14px; text-align:center; }
  </style>
  <?php
    // Same header/nav as leaseView.php
    $tailwind_mode = true;
    require_once('header.php');
  ?>
</head>

<body>

  <div class="hero-header">
    <div class="center-header">
      <h1>Maintenance Requests</h1>
    </div>
  </div>

  <main>
    <div class="main-content-box p-6">

      <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="text-gray-600">
          <?= count($rows) ?> total requests • Sorted by <strong><?= h(ucfirst($sort)) ?></strong> <?= h(strtoupper($dir)) ?>
        </div>
        <div class="page-actions">
          <a class="btn primary" href="addMaintenance.php">+ Add Maintenance Request</a>
        </div>
      </div>

      <div class="table-scroll">
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th><?= sortLink('Date','date',$sort,$dir) ?></th>
              <th><?= sortLink('Type','type',$sort,$dir) ?></th>
              <th><?= sortLink('Address','address',$sort,$dir) ?></th>
              <th>Area</th>
              <th style="width:180px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$rows): ?>
              <tr><td colspan="6" class="text-gray-500">No maintenance requests.</td></tr>
            <?php else: foreach ($rows as $r): 
              $qs = $_GET; $qs['id'] = $r['id'];
              $editUrl = 'editMaintenance.php?'.http_build_query(['id'=>$r['id']]+$_GET);
              $archiveUrl = '?'.http_build_query(['archive'=>$r['id']] + $_GET);
            ?>
              <tr>
                <td><?= h($r['name']) ?></td>
                <td><?= h($r['date']) ?></td>
                <td><?= h($r['type']) ?></td>
                <td><?= h($r['address']) ?></td>
                <td><?= h($r['area']) ?></td>
                <td>
                  <div class="actions">
                    <a class="btn gray" href="<?= h($editUrl) ?>">Edit</a>
                    <a class="btn warn" href="<?= h($archiveUrl) ?>" onclick="return confirm('Archive this request?');">Archive</a>
                  </div>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>

      <div class="d-flex justify-content-end mt-3">
        <a class="btn gray" href="micahportal.php">Return to Portal</a>
      </div>

      <footer class="site-footer">
        Micah Ministries • Maintenance Module (demo) — no database connection active
      </footer>

    </div>
  </main>

</body>
</html>
