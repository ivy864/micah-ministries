<?php
require_once('database/dbLeases.php');

if (!isset($_GET['id'])) {
    http_response_code(400);
    exit("No lease ID provided.");
}

$pdf = get_lease_pdf_file($_GET['id']);

if (!$pdf || !$pdf['lease_form']) {
    http_response_code(404);
    exit("PDF not found.");
}

header("Content-Type: application/pdf");
header("Content-Disposition: inline; filename=\"lease_" . htmlspecialchars($_GET['id']) . ".pdf\"");
echo $pdf['lease_form'];
exit;
?>