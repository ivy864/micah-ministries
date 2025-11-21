<?php
session_cache_expire(30);
session_start();

$loggedIn = false;
$accessLevel = 0;

if (isset($_SESSION['_id'])) {
    $loggedIn = true;
    $accessLevel = $_SESSION['access_level'];
}

// Require at least standard user access
if ($accessLevel < 1) {
    header('Location: index.php');
    die();
}

$lease_id = $_GET['id'] ?? null;

if (!$lease_id) {
    die('No lease ID provided');
}

try {
    $conf = null;
    $confPath = __DIR__ . '/database/dbViewLease.php';

    if (file_exists($confPath)) {
        include $confPath;
    }

    $dsn  = $conf['dsn']  ?? getenv('MICAH_DB_DSN');
    $user = $conf['user'] ?? getenv('MICAH_DB_USER');
    $pass = $conf['pass'] ?? getenv('MICAH_DB_PASS');

    if (!$dsn) {
        die('Database not configured');
    }

    $pdo = new PDO($dsn, $user ?: null, $pass ?: null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // Fetch the lease_form blob
    $stmt = $pdo->prepare("SELECT lease_form FROM dbleases WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $lease_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        die('Lease not found');
    }

    if (empty($row['lease_form'])) {
        die('Lease document not found or empty');
    }

    // Clear any output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }

    // Set headers for PDF display
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="lease_' . htmlspecialchars($lease_id) . '.pdf"');
    header('Content-Length: ' . strlen($row['lease_form']));
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    
    // Output the PDF binary data
    echo $row['lease_form'];
    exit();

} catch (Exception $e) {
    error_log("PDF viewing error: " . $e->getMessage());
    die('Error retrieving lease document: ' . htmlspecialchars($e->getMessage()));
}