<?php
require_once '../../config/database.php';
require_once '../../utils/session.php';

setCorsHeaders();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$username = isset($input['username']) ? trim($input['username']) : '';
$password = isset($input['password']) ? trim($input['password']) : '';

// Validate input
if (empty($username) || empty($password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Username and password are required'
    ]);
    exit;
}

// Query to find user
$query = "SELECT user_id, username, password, email, full_name, role, phone, address, status 
          FROM users 
          WHERE username = ? AND status = 'active'";

$result = executeQuery($query, array($username));

if (!$result['success']) {
    echo json_encode([
        'success' => false,
        'message' => 'Login failed',
        'error' => $result['error']
    ]);
    exit;
}

$user = fetchOne($result['statement']);

if (!$user) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid username or password'
    ]);
    exit;
}

// password verification using hashed comparison
if (!password_verify($password, $user['password'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid username or password'
    ]);
    exit;
}

// Set session variables
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $user['role'];
$_SESSION['full_name'] = $user['full_name'];
$_SESSION['email'] = $user['email'];

// Return success response
echo json_encode([
    'success' => true,
    'message' => 'Login successful',
    'user' => [
        'user_id' => $user['user_id'],
        'username' => $user['username'],
        'full_name' => $user['full_name'],
        'email' => $user['email'],
        'role' => $user['role'],
        'phone' => $user['phone'],
        'address' => $user['address']
    ]
]);
?>
