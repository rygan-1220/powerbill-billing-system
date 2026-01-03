<?php
require_once '../../config/database.php';
require_once '../../utils/session.php';

setCorsHeaders();
requireAnyRole(['admin', 'staff']);

// Get all inquiries
$query = "SELECT i.inquiry_id, i.user_id, i.subject, i.message, i.status, i.response, 
          i.responded_by, i.created_at, i.responded_at,
          u.username, u.full_name, u.email
          FROM inquiries i
          INNER JOIN users u ON i.user_id = u.user_id
          ORDER BY i.created_at DESC";

$result = executeQuery($query);

if (!$result['success']) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch inquiries',
        'error' => $result['error']
    ]);
    exit;
}

$inquiries = fetchAll($result['statement']);

echo json_encode([
    'success' => true,
    'inquiries' => $inquiries
]);
?>
