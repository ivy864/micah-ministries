<?php
// CRITICAL: No whitespace or output before this line!
// Turn off output buffering and error display that might interfere
ini_set('display_errors', 0);
error_reporting(0);

// Clean any output buffers that might have been started
while (ob_get_level()) {
    ob_end_clean();
}

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

// Clear any accidental output
ob_clean();

// Set headers
header("Content-Type: application/pdf");
header("Content-Length: " . strlen($pdf['lease_form']));
header("Content-Disposition: inline; filename=\"lease_" . htmlspecialchars($_GET['id']) . ".pdf\"");
header("Cache-Control: private, max-age=0, must-revalidate");
header("Pragma: public");

// Output the PDF
echo $pdf['lease_form'];
exit;