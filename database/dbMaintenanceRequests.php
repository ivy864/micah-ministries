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
 * dbmaintenancerequests table in the database.
 * @version December 2024
 * @author Micah Ministries Development Team
 */

include_once('dbinfo.php');

/**
 * Sets up a new dbmaintenancerequests table - only creates if it doesn't exist
 */
function create_dbMaintenanceRequests() {
    $con=connect();
    
    // Check if table already exists
    $check_query = "SHOW TABLES LIKE 'dbmaintenancerequests'";
    $result = mysqli_query($con, $check_query);
    
    if (mysqli_num_rows($result) > 0) {
        echo "Table 'dbmaintenancerequests' already exists. No changes made.";
        mysqli_close($con);
        return true;
    }
    
    // Only create table if it doesn't exist
    $result = mysqli_query($con,"CREATE TABLE dbmaintenancerequests (
        id varchar(256) NOT NULL,
        requester_name varchar(100) NOT NULL,
        requester_email varchar(100),
        requester_phone varchar(20),
        location varchar(100),
        building varchar(50),
        room varchar(50),
        description text NOT NULL,
        priority enum('Low','Medium','High','Emergency') DEFAULT 'Medium',
        status enum('Pending','In Progress','Completed','Cancelled') DEFAULT 'Pending',
        assigned_to varchar(100),
        estimated_cost decimal(10,2),
        actual_cost decimal(10,2),
        created_at timestamp DEFAULT CURRENT_TIMESTAMP,
        updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        completed_at timestamp NULL,
        notes text,
        archived tinyint(1) NOT NULL DEFAULT '0',
        PRIMARY KEY (id)
    )");
    
    if (!$result) {
        echo "Error creating table: " . mysqli_error($con);
        mysqli_close($con);
        return false;
    }
    
    echo "Table 'dbmaintenancerequests' created successfully!";
    mysqli_close($con);
    return true;
}

/**
 * adds a new maintenance request to the database
 */
function add_maintenance_request($id, $requester_name, $requester_email, $requester_phone, $location, $building, $unit, $description, $priority, $status, $assigned_to, $notes) {
    $con=connect();
    $query = "INSERT INTO dbmaintenancerequests (id, requester_name, requester_email, requester_phone, location, building, unit, description, priority, status, assigned_to, notes, archived) VALUES('" .
            $id . "','" .
            $requester_name . "','" .
            $requester_email . "','" .
            $requester_phone . "','" .
            $location . "','" .
            $building . "','" .
            $unit . "','" .
            $description . "','" .
            $priority . "','" .
            $status . "','" .
            $assigned_to . "','" .
            $notes . "',0)";
    
    $result = mysqli_query($con,$query);
    if (!$result) {
        echo mysqli_error($con);
        mysqli_close($con);
        return false;
    }
    mysqli_close($con);
    return true;
}

/**
 * retrieves all maintenance requests from the database
 */
function get_all_maintenance_requests() {
    $con=connect();
    $query = "SELECT * FROM dbmaintenancerequests WHERE archived = 0 ORDER BY created_at DESC";
    $result = mysqli_query($con,$query);
    if (!$result) {
        echo mysqli_error($con);
        mysqli_close($con);
        return array();
    }
    
    $requests = array();
    while ($row = mysqli_fetch_array($result)) {
        $requests[] = $row;
    }
    mysqli_close($con);
    return $requests;
}

/**
 * retrieves pending maintenance requests
 */
function get_pending_maintenance_requests() {
    $con=connect();
    $query = "SELECT * FROM dbmaintenancerequests WHERE status = 'Pending' AND archived = 0 ORDER BY priority DESC, created_at ASC";
    $result = mysqli_query($con,$query);
    if (!$result) {
        echo mysqli_error($con);
        mysqli_close($con);
        return array();
    }
    
    $requests = array();
    while ($row = mysqli_fetch_array($result)) {
        $requests[] = $row;
    }
    mysqli_close($con);
    return $requests;
}

/**
 * retrieves a specific maintenance request by id
 */
function get_maintenance_request_by_id($id) {
    $con=connect();
    $query = "SELECT * FROM dbmaintenancerequests WHERE id = '" . $id . "'";
    $result = mysqli_query($con,$query);
    if (!$result || mysqli_num_rows($result) == 0) {
        mysqli_close($con);
        return null;
    }
    
    $row = mysqli_fetch_array($result);
    mysqli_close($con);
    return $row;
}

/**
 * updates a maintenance request
 */
function update_maintenance_request($id, $requester_name, $requester_email, $requester_phone, $location, $building, $unit, $description, $priority, $status, $assigned_to, $notes) {
    $con=connect();
    $query = "UPDATE dbmaintenancerequests SET " .
            "requester_name = '" . $requester_name . "'," .
            "requester_email = '" . $requester_email . "'," .
            "requester_phone = '" . $requester_phone . "'," .
            "location = '" . $location . "'," .
            "building = '" . $building . "'," .
            "unit = '" . $unit . "'," .
            "description = '" . $description . "'," .
            "priority = '" . $priority . "'," .
            "status = '" . $status . "'," .
            "assigned_to = '" . $assigned_to . "'," .
            "notes = '" . $notes . "'" .
            " WHERE id = '" . $id . "'";
    
    $result = mysqli_query($con,$query);
    if (!$result) {
        echo mysqli_error($con);
        mysqli_close($con);
        return false;
    }
    mysqli_close($con);
    return true;
}

/**
 * deletes a maintenance request (archives it)
 */
function delete_maintenance_request($id) {
    $con=connect();
    $query = "UPDATE dbmaintenancerequests SET archived = 1 WHERE id = '" . $id . "'";
    $result = mysqli_query($con,$query);
    if (!$result) {
        echo mysqli_error($con);
        mysqli_close($con);
        return false;
    }
    mysqli_close($con);
    return true;
}

/**
 * gets count of pending maintenance requests
 */
function get_pending_maintenance_count() {
    $con=connect();
    $query = "SELECT COUNT(*) as count FROM dbmaintenancerequests WHERE status = 'Pending' AND archived = 0";
    $result = mysqli_query($con,$query);
    if (!$result) {
        mysqli_close($con);
        return 0;
    }
    
    $row = mysqli_fetch_array($result);
    mysqli_close($con);
    return $row['count'];
}

?>
