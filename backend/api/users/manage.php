<?php
require_once '../../config/database.php';
require_once '../../utils/session.php';

setCorsHeaders();

// Handle different operations
$method = $_SERVER['REQUEST_METHOD'];

// Admin only for GET, POST, DELETE; Admin and Staff for PUT (edit customers)
if ($method === 'PUT') {
    requireAnyRole(['admin', 'staff']);
} else {
    requireRole('admin');
}

if ($method === 'GET') {
    // Get all users
    $query = "SELECT user_id, username, email, full_name, role, phone, address, status, created_at
              FROM users
              ORDER BY created_at DESC";
    
    $result = executeQuery($query);
    
    if (!$result['success']) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to fetch users',
            'error' => $result['error']
        ]);
        exit;
    }
    
    $users = fetchAll($result['statement']);
    
    echo json_encode([
        'success' => true,
        'users' => $users
    ]);
    
} elseif ($method === 'POST') {
    // Create new user
    $input = json_decode(file_get_contents('php://input'), true);
    
    $username = isset($input['username']) ? trim($input['username']) : '';
    $password = isset($input['password']) ? trim($input['password']) : '';
    $email = isset($input['email']) ? trim($input['email']) : '';
    $fullName = isset($input['full_name']) ? trim($input['full_name']) : '';
    $role = isset($input['role']) ? trim($input['role']) : '';
    $phone = isset($input['phone']) ? trim($input['phone']) : '';
    $address = isset($input['address']) ? trim($input['address']) : '';
    
    // Validate input
    if (empty($username) || empty($password) || empty($email) || empty($fullName) || empty($role)) {
        echo json_encode([
            'success' => false,
            'message' => 'All required fields must be filled'
        ]);
        exit;
    }
    
    // Insert user
    $query = "INSERT INTO users (username, password, email, full_name, role, phone, address)
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $result = executeQuery($query, array($username, $password, $email, $fullName, $role, $phone, $address));
    
    if (!$result['success']) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create user',
            'error' => $result['error']
        ]);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'User created successfully'
    ]);
    
} elseif ($method === 'PUT') {
    // Update user
    $input = json_decode(file_get_contents('php://input'), true);
    
    $userId = isset($input['user_id']) ? intval($input['user_id']) : 0;
    $email = isset($input['email']) ? trim($input['email']) : '';
    $fullName = isset($input['full_name']) ? trim($input['full_name']) : '';
    $phone = isset($input['phone']) ? trim($input['phone']) : '';
    $address = isset($input['address']) ? trim($input['address']) : '';
    $status = isset($input['status']) ? trim($input['status']) : '';
    
    if ($userId === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'User ID is required'
        ]);
        exit;
    }
    
    $query = "UPDATE users SET email = ?, full_name = ?, phone = ?, address = ?, status = ?
              WHERE user_id = ?";
    
    $result = executeQuery($query, array($email, $fullName, $phone, $address, $status, $userId));
    
    if (!$result['success']) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update user',
            'error' => $result['error']
        ]);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'User updated successfully'
    ]);
    
} elseif ($method === 'DELETE') {
    // Delete user (set status to inactive)
    $input = json_decode(file_get_contents('php://input'), true);
    
    $userId = isset($input['user_id']) ? intval($input['user_id']) : 0;
    
    if ($userId === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'User ID is required'
        ]);
        exit;
    }
    
    $query = "UPDATE users SET status = 'inactive' WHERE user_id = ?";
    
    $result = executeQuery($query, array($userId));
    
    if (!$result['success']) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to delete user',
            'error' => $result['error']
        ]);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'User deleted successfully'
    ]);
    
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
