<?php
include_once('dbinfo.php');
include_once(dirname(__FILE__).'/../domain/MaintenanceImage.php');

/**
 * Insert an uploaded image into dbmaintenanceimages
 */
function add_maintenance_image($request_id, $file_name, $file_type, $file_blob) {
    $con = connect();

    $query = "INSERT INTO dbmaintenanceimages (request_id, file_name, file_type, file_blob)
              VALUES (?, ?, ?, ?)";

    $stmt = mysqli_prepare($con, $query);
    if (!$stmt) {
        mysqli_close($con);
        return false;
    }

    mysqli_stmt_bind_param($stmt, "ssss",
        $request_id,
        $file_name,
        $file_type,
        $file_blob
    );

    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($con);

    return (bool)$result;
}


/**
 * Get all images for a given maintenance request
 */
function get_maintenance_images_by_request($request_id) {
    $con = connect();
    $query = "SELECT * FROM dbmaintenanceimages WHERE request_id = ? ORDER BY uploaded_at ASC";

    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "s", $request_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $images = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $images[] = new MaintenanceImage(
            $row['id'],
            $row['request_id'],
            $row['file_name'],
            $row['file_type'],
            $row['file_blob'],
            $row['uploaded_at']
        );
    }

    mysqli_stmt_close($stmt);
    mysqli_close($con);

    return $images;
}


/**
 * Get a single image blob by image ID
 */
function get_maintenance_image_file($id) {
    $con = connect();
    $query = "SELECT file_type, file_blob FROM dbmaintenanceimages WHERE id = ?";

    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $file = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);
    mysqli_close($con);

    return $file ? $file : null;
}

?>
