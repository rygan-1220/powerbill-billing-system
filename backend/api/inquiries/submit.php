<?php
require_once '../../config/database.php';
require_once '../../utils/session.php';

setCorsHeaders();
requireRole('customer');

$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$subject = isset($input['subject']) ? trim($input['subject']) : '';
$message = isset($input['message']) ? trim($input['message']) : '';
$userId = getCurrentUserId();

// Validate input
if (empty($subject) || empty($message)) {
    echo json_encode([
        'success' => false,
        'message' => 'Subject and message are required'
    ]);
    exit;
}

// Insert inquiry
$query = "INSERT INTO inquiries (user_id, subject, message, status)
          VALUES (?, ?, ?, 'open')";

$result = executeQuery($query, array($userId, $subject, $message));

if (!$result['success']) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to submit inquiry',
        'error' => $result['error']
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Inquiry submitted successfully'
]);
?>
