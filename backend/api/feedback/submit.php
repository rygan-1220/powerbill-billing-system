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

$rating = isset($input['rating']) ? intval($input['rating']) : 0;
$comments = isset($input['comments']) ? trim($input['comments']) : '';
$category = isset($input['category']) ? trim($input['category']) : '';
$userId = getCurrentUserId();

// Validate input
if ($rating < 1 || $rating > 5) {
    echo json_encode([
        'success' => false,
        'message' => 'Rating must be between 1 and 5'
    ]);
    exit;
}

// Insert feedback
$query = "INSERT INTO feedback (user_id, rating, comments, category)
          VALUES (?, ?, ?, ?)";

$result = executeQuery($query, array($userId, $rating, $comments, $category));

if (!$result['success']) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to submit feedback',
        'error' => $result['error']
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Feedback submitted successfully'
]);
?>
