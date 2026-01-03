<?php
require_once '../../config/database.php';
require_once '../../utils/session.php';

setCorsHeaders();
requireLogin();

$method = $_SERVER['REQUEST_METHOD'];
$userId = getCurrentUserId();

if ($method === 'GET') {
    $query = "SELECT user_id, username, email, full_name, role, phone, address, status, created_at
              FROM users WHERE user_id = ?";
    $result = executeQuery($query, array($userId));

    if (!$result['success']) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to fetch profile',
            'error' => $result['error']
        ]);
        exit;
    }

    $user = fetchOne($result['statement']);
    if (!$user) {
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'user' => $user
    ]);
    exit;
} elseif ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);

    $email = isset($input['email']) ? trim($input['email']) : '';
    $fullName = isset($input['full_name']) ? trim($input['full_name']) : '';
    $phone = isset($input['phone']) ? trim($input['phone']) : '';
    $address = isset($input['address']) ? trim($input['address']) : '';

    if (empty($email) || empty($fullName)) {
        echo json_encode([
            'success' => false,
            'message' => 'Full name and email are required'
        ]);
        exit;
    }

    $query = "UPDATE users SET email = ?, full_name = ?, phone = ?, address = ?, updated_at = GETDATE() WHERE user_id = ?";
    $result = executeQuery($query, array($email, $fullName, $phone, $address, $userId));

    if (!$result['success']) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update profile',
            'error' => $result['error']
        ]);
        exit;
    }

    // Refresh and return updated user
    $getResult = executeQuery("SELECT user_id, username, email, full_name, role, phone, address, status, created_at FROM users WHERE user_id = ?", array($userId));
    $updated = $getResult['success'] ? fetchOne($getResult['statement']) : null;

    // Update session cache for quick references
    if ($updated) {
        $_SESSION['full_name'] = $updated['full_name'];
        $_SESSION['email'] = $updated['email'];
    }

    echo json_encode([
        'success' => true,
        'message' => 'Profile updated successfully',
        'user' => $updated
    ]);
    exit;
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}
?>
