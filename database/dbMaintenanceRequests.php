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
include_once(dirname(__FILE__).'/../domain/MaintenanceRequest.php');

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
        unit varchar(50),
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
 * adds a new maintenance request to the database using MaintenanceRequest object
 */
function add_maintenance_request($maintenanceRequest) {
    if (!$maintenanceRequest instanceof MaintenanceRequest) {
        die("Error: add_maintenance_request type mismatch");
    }

    $con = connect();
    $query = "INSERT INTO dbmaintenancerequests (id, requester_name, requester_email, requester_phone, location, building, unit, description, priority, status, assigned_to, notes, archived, created_at, updated_at, completed_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($con, $query);
    if (!$stmt) {
        mysqli_close($con);
        return false;
    }

    $id = $maintenanceRequest->getID();
    $requester_name = $maintenanceRequest->getRequesterName();
    $requester_email = $maintenanceRequest->getRequesterEmail();
    $requester_phone = $maintenanceRequest->getRequesterPhone();
    $location = $maintenanceRequest->getLocation();
    $building = $maintenanceRequest->getBuilding();
    $unit = $maintenanceRequest->getUnit();
    $description = $maintenanceRequest->getDescription();
    $priority = $maintenanceRequest->getPriority();
    $status = $maintenanceRequest->getStatus();
    $assigned_to = $maintenanceRequest->getAssignedTo();
    $notes = $maintenanceRequest->getNotes();
    $archived = (int)$maintenanceRequest->getArchived();
    $created_at = $maintenanceRequest->getCreatedAt();
    $updated_at = $maintenanceRequest->getUpdatedAt();
    $completed_at = $maintenanceRequest->getCompletedAt();

    mysqli_stmt_bind_param(
        $stmt,
        "ssssssssssssisss",
        $id,
        $requester_name,
        $requester_email,
        $requester_phone,
        $location,
        $building,
        $unit,
        $description,
        $priority,
        $status,
        $assigned_to,
        $notes,
        $archived,
        $created_at,
        $updated_at,
        $completed_at
    );

    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($con);

    return (bool)$result;
}


/**
 * Retrieves maintenance requests as MaintenanceRequest objects.
 * If $archived is true, returns archived (archived=1); otherwise active (archived=0).
 */
function get_all_maintenance_requests($limit = null, $offset = 0, $sort_by = 'created_at', $sort_order = 'DESC', $archived = false) {
    $con = connect();
    
    // validate sort column to prevent SQL injection
    $allowed_sort_columns = ['id', 'requester_name', 'location', 'building', 'priority', 'status', 'assigned_to', 'created_at'];
    if (!in_array($sort_by, $allowed_sort_columns)) {
        $sort_by = 'created_at';
    }
    
    // validate sort order
    $sort_order = strtoupper($sort_order);
    if (!in_array($sort_order, ['ASC', 'DESC'])) {
        $sort_order = 'DESC';
    }
    
    // ensure offset is never negative
    $offset = max(0, (int)$offset);

    // NEW: archived toggle
    $archived_value = $archived ? 1 : 0;

    
    $query = "SELECT * FROM dbmaintenancerequests 
              WHERE archived = " . (int)$archived_value . " 
              ORDER BY " . $sort_by . " " . $sort_order;
    
    if ($limit !== null) {
        $query .= " LIMIT " . (int)$limit . " OFFSET " . $offset;
    }
    
    $result = mysqli_query($con, $query);
    if (!$result) {
        echo mysqli_error($con);
        mysqli_close($con);
        return array();
    }
    
    $requests = array();
    while ($row = mysqli_fetch_array($result)) {
        $request = new MaintenanceRequest(
            $row['id'],
            $row['requester_name'],
            $row['requester_email'],
            $row['requester_phone'],
            $row['location'],
            $row['building'],
            $row['unit'],
            $row['description'],
            $row['priority'],
            $row['status'],
            $row['assigned_to'],
            $row['notes'],
            $row['archived']
        );
        
        // set timestamps from database
        $request->setCreatedAt($row['created_at']);
        $request->setUpdatedAt($row['updated_at']);
        if ($row['completed_at']) {
            $request->setCompletedAt($row['completed_at']);
        }
        
        $requests[] = $request;
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
 * retrieves a specific maintenance request by id as MaintenanceRequest object
 */
function get_maintenance_request_by_id($id) {
    $con = connect();
    $query = "SELECT * FROM dbmaintenancerequests WHERE id = ?";

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
    $request = new MaintenanceRequest(
        $row['id'],
        $row['requester_name'],
        $row['requester_email'],
        $row['requester_phone'],
        $row['location'],
        $row['building'],
        $row['unit'],
        $row['description'],
        $row['priority'],
        $row['status'],
        $row['assigned_to'],
        $row['notes'],
        $row['archived']
    );
    
    // set timestamps from database
    $request->setCreatedAt($row['created_at']);
    $request->setUpdatedAt($row['updated_at']);
    if ($row['completed_at']) {
        $request->setCompletedAt($row['completed_at']);
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($con);
    return $request;
}


/**
 * updates a maintenance request using MaintenanceRequest object
 */
function update_maintenance_request($maintenanceRequest) {
    if (!$maintenanceRequest instanceof MaintenanceRequest) {
        die("Error: update_maintenance_request type mismatch");
    }

    $con = connect();
    $query = "UPDATE dbmaintenancerequests SET 
            requester_name = ?, 
            requester_email = ?, 
            requester_phone = ?, 
            location = ?, 
            building = ?, 
            unit = ?, 
            description = ?, 
            priority = ?, 
            status = ?, 
            assigned_to = ?, 
            notes = ?, 
            archived = ?, 
            updated_at = ?, 
            completed_at = ?
        WHERE id = ?";

    $stmt = mysqli_prepare($con, $query);
    if (!$stmt) {
        mysqli_close($con);
        return false;
    }

    $requester_name = $maintenanceRequest->getRequesterName();
    $requester_email = $maintenanceRequest->getRequesterEmail();
    $requester_phone = $maintenanceRequest->getRequesterPhone();
    $location = $maintenanceRequest->getLocation();
    $building = $maintenanceRequest->getBuilding();
    $unit = $maintenanceRequest->getUnit();
    $description = $maintenanceRequest->getDescription();
    $priority = $maintenanceRequest->getPriority();
    $status = $maintenanceRequest->getStatus();
    $assigned_to = $maintenanceRequest->getAssignedTo();
    $notes = $maintenanceRequest->getNotes();
    $archived = (int)$maintenanceRequest->getArchived();
    $updated_at = $maintenanceRequest->getUpdatedAt();
    $completed_at = $maintenanceRequest->getCompletedAt();
    $id = $maintenanceRequest->getID();

    mysqli_stmt_bind_param(
        $stmt,
        "sssssssssssisss",
        $requester_name,
        $requester_email,
        $requester_phone,
        $location,
        $building,
        $unit,
        $description,
        $priority,
        $status,
        $assigned_to,
        $notes,
        $archived,
        $updated_at,
        $completed_at,
        $id
    );

    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($con);

    return (bool)$result;
}


/**
 * deletes a maintenance request (archives it)
 */
function delete_maintenance_request($id) {
    $con = connect();
    $query = "UPDATE dbmaintenancerequests SET archived = 1 WHERE id = ?";

    $stmt = mysqli_prepare($con, $query);
    if (!$stmt) {
        mysqli_close($con);
        return false;
    }

    mysqli_stmt_bind_param($stmt, "s", $id);
    $result = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);
    mysqli_close($con);
    return (bool)$result;
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

/**
 * gets total count of all maintenance requests
 */
/**
 * Gets total count of maintenance requests
 * (active by default; archived when $archived = true)
 */
function get_maintenance_requests_count($archived = false) {
    $con = connect();
    $archived_value = $archived ? 1 : 0;

    $query = "SELECT COUNT(*) as count 
              FROM dbmaintenancerequests 
              WHERE archived = " . (int)$archived_value;

    $result = mysqli_query($con, $query);
    if (!$result) {
        mysqli_close($con);
        return 0;
    }
    
    $row = mysqli_fetch_array($result);
    mysqli_close($con);
    return (int)$row['count'];
}


?>
