<?php
require_once '../../config/database.php';
require_once '../../utils/session.php';

setCorsHeaders();
requireAnyRole(['admin', 'staff']);

$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$inquiryId = isset($input['inquiry_id']) ? intval($input['inquiry_id']) : 0;
$response = isset($input['response']) ? trim($input['response']) : '';
$adminId = getCurrentUserId();

// Validate input
if ($inquiryId === 0 || empty($response)) {
    echo json_encode([
        'success' => false,
        'message' => 'Inquiry ID and response are required'
    ]);
    exit;
}

// Update inquiry
$query = "UPDATE inquiries 
          SET response = ?, responded_by = ?, responded_at = GETDATE(), status = 'resolved'
          WHERE inquiry_id = ?";

$result = executeQuery($query, array($response, $adminId, $inquiryId));

if (!$result['success']) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to respond to inquiry',
        'error' => $result['error']
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Response sent successfully'
]);
?>
