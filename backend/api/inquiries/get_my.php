<?php
require_once '../../config/database.php';
require_once '../../utils/session.php';

setCorsHeaders();

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

requireRole('customer');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$userId = getCurrentUserId();

// Get inquiries for this customer
$query = "SELECT i.inquiry_id, i.subject, i.message, i.status, i.response, 
                 i.created_at, i.responded_at,
                 u.full_name as responded_by_name
          FROM inquiries i
          LEFT JOIN users u ON i.responded_by = u.user_id
          WHERE i.user_id = ?
          ORDER BY i.created_at DESC";

$result = executeQuery($query, array($userId));

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
