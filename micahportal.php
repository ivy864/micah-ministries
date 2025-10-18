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
    <title>Micah Portal</title>
</head>
<body>
    <h2>Welcome</h2>

    <button onclick="window.location.href='leaseman.php'">Lease Management</button>
    <button onclick="window.location.href='maintman.php'">Maintenance Management</button>
    <button onclick="window.location.href='editlogin.php'">Edit Profile</button>
</body>
</html>
