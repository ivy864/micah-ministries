<?php
// database configuration for lease management
$conf = [
    'dsn' => 'mysql:host=localhost;dbname=micah-ministries-db',
    'user' => 'micah-ministries-db',
    'pass' => 'micah-ministries-db'
];

// production server configuration
if ($_SERVER['SERVER_NAME'] == 'jenniferp214.sg-host.com') {
        $user = 'ufn0uoxjv4xze';
        $database = 'dbg3egamdqoqhn';
        $pass = 'k9p3snb93cwe';
}

// delete_lease function moved to database/dbLeases.php
// include it here for backward compatibility
if (!function_exists('delete_lease')) {
    include_once('database/dbLeases.php');
}
?>
