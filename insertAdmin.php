<?php
    ini_set("display_errors",1);
    error_reporting(E_ALL);
    include_once('domain/Person.php');
    include_once('database/dbPersons.php');

    // Function to create a test user
    function createTestUser($username, $first_name, $last_name, $email, $type, $password) {
        $person = Array();
        $person['id'] = $username;
        $person['first_name'] = $first_name;
        $person['last_name'] = $last_name;
        $person['venue'] = 'portland';
        $person['street_address'] = '123 Test Street';
        $person['city'] = 'Test City';
        $person['state'] = 'VA';
        $person['zip_code'] = '12345';
        $person['phone1'] = '5551234567';
        $person['phone1type'] = 'cellphone';
        $person['phone2'] = 'N/A';
        $person['phone2type'] = 'N/A';
        $person['email'] = $email;
        $person['emergency_contact_first_name'] = 'Test';
        $person['emergency_contact_last_name'] = 'Contact';
        $person['emergency_contact_phone'] = '5559876543';
        $person['emergency_contact_phone_type'] = 'cellphone';
        $person['emergency_contact_relation'] = 'Friend';
        $person['type'] = $type;
        $person['status'] = 'Active';
        $person['archived'] = 0;
        $person['skills'] = '';
        $person['interests'] = '';
        $person['training_level'] = 'None';
        $person['is_community_service_volunteer'] = 0;
        $person['is_new_volunteer'] = 0;
        $person['total_hours_volunteered'] = 0.00;
        $person['birthday'] = '1990-01-01';
        $person['start_date'] = date('Y-m-d');
        $person['notes'] = 'Test user for ' . $type . ' role';
        $person['password'] = password_hash($password, PASSWORD_BCRYPT);
        $person['profile_pic'] = '';
        $person['gender'] = '';
        $person['force_password_change'] = 0;
        
        $days = array('sun', 'mon', 'tues', 'wednes', 'thurs', 'fri', 'satur');
        foreach ($days as $day) {
            $person[$day . 'days_start'] = '';
            $person[$day . 'days_end'] = '';
        }
        
        $PERSON = make_a_person($person);
        return add_person($PERSON);
    }

    echo "<h2>Creating Test Users for Micah Ministries</h2>";
    echo "<div style='font-family: monospace; margin: 20px;'>";

    // Create vmsroot (keep existing)
    $person = Array();
    $person['id'] = 'vmsroot';
    $person['first_name'] = 'vmsroot';
    $person['last_name'] = '';
    $person['venue'] = 'portland';
    $person['street_address'] = 'N/A';
    $person['city'] = 'N/A';
    $person['state'] = 'VA';
    $person['zip_code'] = 'N/A';
    $person['phone1'] = '';
    $person['phone1type'] = 'N/A';
    $person['phone2'] = 'N/A';
    $person['phone2type'] = 'N/A';
    $person['email'] = 'vmsroot';
    $person['emergency_contact_first_name'] = 'N/A';
    $person['emergency_contact_last_name'] = 'N/A';
    $person['emergency_contact_phone'] = 'N/A';
    $person['emergency_contact_phone_type'] = 'N/A';
    $person['emergency_contact_relation'] = 'N/A';
    $person['type'] = '';
    $person['status'] = 'N/A';
    $person['archived'] = 0;
    $person['skills'] = 'N/A';
    $person['interests'] = 'N/A';
    $person['training_level'] = 'N/A';
    $person['is_community_service_volunteer'] = 0;
    $person['is_new_volunteer'] = 0;
    $person['total_hours_volunteered'] = 0.00;
    $person['birthday'] = 'N/A';
    $person['start_date'] = 'N/A';
    $person['notes'] = 'N/A';
    $person['password'] = password_hash('vmsroot', PASSWORD_BCRYPT);
    $person['profile_pic'] = '';
    $person['gender'] = '';
    $person['force_password_change'] = 1;
    $days = array('sun', 'mon', 'tues', 'wednes', 'thurs', 'fri', 'satur');
    foreach ($days as $day) {
        $person[$day . 'days_start'] = '';
        $person[$day . 'days_end'] = '';
    }
    $PERSON = make_a_person($person);
    $result = add_person($PERSON);
    if ($result) {
        echo "✅ vmsroot (Super Admin) - CREATED<br>";
    } else {
        echo "ℹ️ vmsroot (Super Admin) - ALREADY EXISTS<br>";
    }

    // Create testadmin
    $result = createTestUser('testadmin', 'Test', 'Admin', 'testadmin@micahministries.org', 'admin', 'testadmin123');
    if ($result) {
        echo "✅ testadmin (Admin) - CREATED<br>";
    } else {
        echo "ℹ️ testadmin (Admin) - ALREADY EXISTS<br>";
    }

    // Create testcasemanager
    $result = createTestUser('testcasemanager', 'Test', 'Case Manager', 'testcasemanager@micahministries.org', 'case_manager', 'testcasemanager123');
    if ($result) {
        echo "✅ testcasemanager (Case Manager) - CREATED<br>";
    } else {
        echo "ℹ️ testcasemanager (Case Manager) - ALREADY EXISTS<br>";
    }

    // Create testmaintenance
    $result = createTestUser('testmaintenance', 'Test', 'Maintenance', 'testmaintenance@micahministries.org', 'maintenance', 'testmaintenance123');
    if ($result) {
        echo "✅ testmaintenance (Maintenance Staff) - CREATED<br>";
    } else {
        echo "ℹ️ testmaintenance (Maintenance Staff) - ALREADY EXISTS<br>";
    }

    echo "</div>";
    echo "<h3>Test User Credentials:</h3>";
    echo "<ul>";
    echo "<li><strong>vmsroot</strong> / vmsroot (Super Admin - Level 3)</li>";
    echo "<li><strong>testadmin</strong> / testadmin123 (Admin - Level 3)</li>";
    echo "<li><strong>testcasemanager</strong> / testcasemanager123 (Case Manager - Level 2)</li>";
    echo "<li><strong>testmaintenance</strong> / testmaintenance123 (Maintenance Staff - Level 1)</li>";
    echo "</ul>";
    echo "<p><a href='login.php'>Go to Login Page</a></p>";
?>