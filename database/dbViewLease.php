<?php
// database configuration for lease management
$conf = [
    'dsn' => 'mysql:host=localhost;dbname=micah-ministries-db',
    'user' => 'micah-ministries-db',
    'pass' => 'micah-ministries-db'
];

// production server configuration
if ($_SERVER['SERVER_NAME'] == 'jenniferp160.sg-host.com') {
    $conf = [
        'dsn' => 'mysql:host=localhost;dbname=dbkzrh4cfmxbt0',
        'user' => 'uknrzrk8sj1e7',
        'pass' => 'fxextih7mssg'
    ];
}

function delete_lease($lease_id) {    
    include_once('database/dbinfo.php'); 
    $connection=connect();  
    $query = "DELETE FROM dbleases WHERE id LIKE '$lease_id'";
    $result = mysqli_query($connection, $query);
    $result = boolval($result);
    mysqli_close($connection);
    return $result;
}
?>
