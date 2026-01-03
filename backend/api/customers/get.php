<?php
require_once '../../config/database.php';
require_once '../../utils/session.php';

setCorsHeaders();
requireRole('staff');

// Get all customers
$query = "SELECT user_id, username, email, full_name, phone, address, status, created_at
          FROM users
          WHERE role = 'customer'
          ORDER BY created_at DESC";

$result = executeQuery($query);

if (!$result['success']) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch customers',
        'error' => $result['error']
    ]);
    exit;
}

$customers = fetchAll($result['statement']);

echo json_encode([
    'success' => true,
    'customers' => $customers
]);
?>
