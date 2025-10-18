<?php
session_start();

// redirect to login if not logged in
if (!isset($_SESSION['_id'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Lease Management</title>
</head>
<body>
    <h2>Lease Management Page</h2>
    <p>This is where lease-related features will go.</p>
    <a href="micahportal.php">Return to Portal</a>
</body>
</html>
