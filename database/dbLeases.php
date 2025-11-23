<?php
/*
 * Copyright 2024 by Micah Ministries. 
 * This program is part of Micah Ministries VMS, which is free software.  It comes with 
 * absolutely no warranty. You can redistribute and/or modify it under the terms 
 * of the GNU General Public License as published by the Free Software Foundation
 * (see <http://www.gnu.org/licenses/ for more information).
 * 
 */

/**
 * Functions to create, update, and retrieve information from the
 * dbleases table in the database.
 * @version December 2024
 * @author Micah Ministries Development Team
 */

include_once('dbinfo.php');
include_once(dirname(__FILE__).'/../domain/Lease.php');

/**
 * adds a new lease to the database using Lease object
 */
/**
 * adds a new lease to the database using Lease object
 */
function add_lease($lease) {
    if (!$lease instanceof Lease) {
        die("Error: add_lease type mismatch");
    }

    $con = connect();
    
    // Remove created_at and updated_at - they auto-populate
    $query = "INSERT INTO dbleases (
        id, tenant_first_name, tenant_last_name, property_street, 
        unit_number, property_city, property_state, property_zip, 
        start_date, expiration_date, monthly_rent, security_deposit, 
        lease_form, case_manager, program_type, status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($con, $query);
    if (!$stmt) {
        error_log("Prepare failed: " . mysqli_error($con));
        mysqli_close($con);
        return false;
    }

    $id = $lease->getID();
    $tenant_first_name = $lease->getTenantFirstName();
    $tenant_last_name = $lease->getTenantLastName();
    $property_street = $lease->getPropertyStreet();
    $unit_number = $lease->getUnitNumber();
    $property_city = $lease->getPropertyCity();
    $property_state = $lease->getPropertyState();
    $property_zip = $lease->getPropertyZip();
    $start_date = $lease->getStartDate();
    $expiration_date = $lease->getExpirationDate();
    $monthly_rent = $lease->getMonthlyRent();
    $security_deposit = $lease->getSecurityDeposit();
    $lease_form = $lease->getLeaseForm();
    $case_manager = $lease->getCaseManager();
    $program_type = $lease->getProgramType();
    $status = $lease->getStatus();

    // s=string, d=double/decimal, b=blob
    // 12th parameter (index 12) is the blob
    mysqli_stmt_bind_param(
        $stmt,
        "ssssssssssddbsss",  // note: space added for clarity - remove in actual code
        $id,
        $tenant_first_name,
        $tenant_last_name,
        $property_street,
        $unit_number,
        $property_city,
        $property_state,
        $property_zip,
        $start_date,
        $expiration_date,
        $monthly_rent,      // d = decimal
        $security_deposit,  // d = decimal
        $lease_form,        // b = blob
        $case_manager,
        $program_type,
        $status
    );

    // Send blob data separately if it exists
    if ($lease_form !== null && strlen($lease_form) > 0) {
        mysqli_stmt_send_long_data($stmt, 12, $lease_form); // 12 is the index of lease_form (0-based)
        error_log("Sending PDF blob: " . strlen($lease_form) . " bytes");
    }

    $result = mysqli_stmt_execute($stmt);
    
    if (!$result) {
        error_log("Execute failed: " . mysqli_stmt_error($stmt));
    } else {
        error_log("Lease inserted successfully. ID: " . $id);
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($con);
    return $result;
}

/**
 * retrieves a specific lease by id as Lease object
 */
function get_lease_by_id($id) {
    $con = connect();
    $query = "SELECT * FROM dbleases WHERE id = ?";
    $stmt = mysqli_prepare($con, $query);
    if (!$stmt) {
        mysqli_close($con);
        return null;
    }
    
    mysqli_stmt_bind_param($stmt, "s", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (!$result || mysqli_num_rows($result) == 0) {
        mysqli_stmt_close($stmt);
        mysqli_close($con);
        return null;
    }
    
    $row = mysqli_fetch_array($result);
    $lease = new Lease(
        $row['id'],
        $row['tenant_first_name'],
        $row['tenant_last_name'],
        $row['property_street'],
        $row['unit_number'],
        $row['property_city'],
        $row['property_state'],
        $row['property_zip'],
        $row['start_date'],
        $row['expiration_date'],
        $row['monthly_rent'],
        $row['security_deposit'],
        $row['lease_form'],
        $row['case_manager'],
        $row['program_type'],
        $row['status']
    );
    
    // set timestamps from database
    $lease->setCreatedAt($row['created_at']);
    $lease->setUpdatedAt($row['updated_at']);
    
    mysqli_stmt_close($stmt);
    mysqli_close($con);
    return $lease;
}

/**
 * updates a lease using Lease object
 */
/**
 * updates a lease using Lease object
 */
function update_lease($lease) {
    if (!$lease instanceof Lease) {
        die("Error: update_lease type mismatch");
    }

    $con = connect();
    $query = "UPDATE dbleases SET 
        tenant_first_name = ?, tenant_last_name = ?, property_street = ?, 
        unit_number = ?, property_city = ?, property_state = ?, property_zip = ?, 
        start_date = ?, expiration_date = ?, monthly_rent = ?, security_deposit = ?, 
        lease_form = ?, case_manager = ?, program_type = ?, status = ?
        WHERE id = ?";

    $stmt = mysqli_prepare($con, $query);
    if (!$stmt) {
        error_log("Prepare failed: " . mysqli_error($con));
        mysqli_close($con);
        return false;
    }

    $id = $lease->getID();
    $tenant_first_name = $lease->getTenantFirstName();
    $tenant_last_name = $lease->getTenantLastName();
    $property_street = $lease->getPropertyStreet();
    $unit_number = $lease->getUnitNumber();
    $property_city = $lease->getPropertyCity();
    $property_state = $lease->getPropertyState();
    $property_zip = $lease->getPropertyZip();
    $start_date = $lease->getStartDate();
    $expiration_date = $lease->getExpirationDate();
    $monthly_rent = $lease->getMonthlyRent();
    $security_deposit = $lease->getSecurityDeposit();
    $lease_form = $lease->getLeaseForm();
    $case_manager = $lease->getCaseManager();
    $program_type = $lease->getProgramType();
    $status = $lease->getStatus();

    mysqli_stmt_bind_param(
    $stmt,
    "sssssssssddbssss",  // ✅ CORRECT - 15 characters
    $tenant_first_name,
    $tenant_last_name,
    $property_street,
    $unit_number,
    $property_city,
    $property_state,
    $property_zip,
    $start_date,
    $expiration_date,
    $monthly_rent,
    $security_deposit,
    $lease_form,
    $case_manager,
    $program_type,
    $status,
    $id
);

    // Send blob data separately if it exists
    if ($lease_form !== null && strlen($lease_form) > 0) {
        mysqli_stmt_send_long_data($stmt, 11, $lease_form); // 11 is the index of lease_form in UPDATE
        error_log("Updating PDF blob: " . strlen($lease_form) . " bytes");
    }

    $result = mysqli_stmt_execute($stmt);
    
    if (!$result) {
        error_log("Update failed: " . mysqli_stmt_error($stmt));
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($con);
    return $result;
}

/**
 * deletes a lease by id
 */
function delete_lease($lease_id) {
    $con = connect();
    
    // use prepared statement to prevent sql injection
    $query = "DELETE FROM dbleases WHERE id = ?";
    $stmt = mysqli_prepare($con, $query);
    if (!$stmt) {
        mysqli_close($con);
        return false;
    }
    
    mysqli_stmt_bind_param($stmt, "s", $lease_id);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($con);
    return boolval($result);
}

?>

