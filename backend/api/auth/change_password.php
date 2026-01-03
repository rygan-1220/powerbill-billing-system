<?php
require_once '../../config/database.php';
require_once '../../utils/session.php';

setCorsHeaders();
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$currentPassword = isset($input['current_password']) ? trim($input['current_password']) : '';
$newPassword = isset($input['new_password']) ? trim($input['new_password']) : '';
$userId = getCurrentUserId();

if (empty($currentPassword) || empty($newPassword)) {
    echo json_encode([
        'success' => false,
        'message' => 'Current and new password are required'
    ]);
    exit;
}

// Verify current password
$query = "SELECT password FROM users WHERE user_id = ?";
$result = executeQuery($query, array($userId));

if (!$result['success']) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to verify password',
        'error' => $result['error']
    ]);
    exit;
}

$user = fetchOne($result['statement']);
if (!$user || !password_verify($currentPassword, $user['password'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Current password is incorrect'
    ]);
    exit;
}

// Hash new password and update it
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
$update = executeQuery("UPDATE users SET password = ?, updated_at = GETDATE() WHERE user_id = ?", array($hashedPassword, $userId));
if (!$update['success']) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to change password',
        'error' => $update['error']
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Password changed successfully'
]);
?>
