<?php
require_once '../config/database.php';
require_once '../utils/session.php';

setCorsHeaders();

// Check session status
$user = getCurrentUser();

echo json_encode([
    'success' => true,
    'session_status' => [
        'is_logged_in' => isLoggedIn(),
        'user_id' => $user['user_id'],
        'username' => $user['username'],
        'role' => $user['role'],
        'full_name' => $user['full_name'],
        'email' => $user['email'],
        'session_id' => session_id()
    ]
]);
?>
