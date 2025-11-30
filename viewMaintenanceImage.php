<?php
require_once('database/dbMaintenanceImages.php');

if (!isset($_GET['id'])) {
    http_response_code(400);
    exit("Missing image ID.");
}

$image = get_maintenance_image_file($_GET['id']);

if (!$image) {
    http_response_code(404);
    exit("Image not found.");
}

header("Content-Type: " . $image['file_type']);
echo $image['file_blob'];
exit;
