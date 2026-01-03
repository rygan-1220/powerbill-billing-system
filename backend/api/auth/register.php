<?php
require_once '../../config/database.php';
require_once '../../utils/session.php';

setCorsHeaders();

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Only POST method is allowed'
    ]);
    exit;
}

// Get input data
$input = json_decode(file_get_contents('php://input'), true);

$username = isset($input['username']) ? trim($input['username']) : '';
$password = isset($input['password']) ? trim($input['password']) : '';
$fullName = isset($input['full_name']) ? trim($input['full_name']) : '';
$role = 'customer'; // Always customer for public registration

// Validate required fields
if (empty($username) || empty($password) || empty($fullName)) {
    echo json_encode([
        'success' => false,
        'message' => 'Username, password, and full name are required'
    ]);
    exit;
}

// Validate username format
if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
    echo json_encode([
        'success' => false,
        'message' => 'Username must be 3-50 characters and contain only letters, numbers, and underscores'
    ]);
    exit;
}

// Validate password length
if (strlen($password) < 6) {
    echo json_encode([
        'success' => false,
        'message' => 'Password must be at least 6 characters'
    ]);
    exit;
}

// Check if username already exists
$checkQuery = "SELECT user_id FROM users WHERE username = ?";
$checkResult = executeQuery($checkQuery, array($username));

if (!$checkResult['success']) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'error' => $checkResult['error']
    ]);
    exit;
}

$existingUser = fetchOne($checkResult['statement']);
if ($existingUser) {
    echo json_encode([
        'success' => false,
        'message' => 'Username already exists. Please choose a different username.'
    ]);
    exit;
}

// Hash password for security
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert new customer user (use empty strings for optional fields)
$insertQuery = "INSERT INTO users (username, password, email, full_name, role, phone, address, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'active')";

$result = executeQuery($insertQuery, array($username, $hashedPassword, '', $fullName, $role, '', '', 'active'));

if (!$result['success']) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create account. Please try again.',
        'error' => $result['error']
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Account created successfully! You can now login with your credentials.'
]);
