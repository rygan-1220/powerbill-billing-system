<?php
require_once '../../config/database.php';
require_once '../../utils/session.php';

setCorsHeaders();
requireRole('staff');

// Get all bills with customer information
$query = "SELECT b.bill_id, b.user_id, b.bill_month, b.previous_reading, b.current_reading, 
          b.units_consumed, b.rate_per_unit, b.total_amount, b.due_date, b.status, b.created_at,
          u.username, u.full_name, u.email, u.phone, u.address
          FROM bills b
          INNER JOIN users u ON b.user_id = u.user_id
          ORDER BY b.created_at DESC";

$result = executeQuery($query);

if (!$result['success']) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch bills',
        'error' => $result['error']
    ]);
    exit;
}

$bills = fetchAll($result['statement']);

echo json_encode([
    'success' => true,
    'bills' => $bills
]);
?>
