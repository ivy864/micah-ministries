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
    <title>Maintenance Management</title>
</head>
<body>
    <h2>Maintenance Management Page</h2>
    <p>This is where maintenance request tools will go.</p>
    <a href="micahportal.php">Return to Portal</a>
</body>
</html>
