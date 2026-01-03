<?php
require_once '../../config/database.php';
require_once '../../utils/session.php';

setCorsHeaders();

// Allow both customer and staff to access
$userRole = getCurrentUserRole();
if ($userRole !== 'customer' && $userRole !== 'staff') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
    exit;
}

// Get customer's bills
// Staff can specify customer_id, customers get their own bills
if ($userRole === 'staff' && isset($_GET['customer_id'])) {
    $userId = $_GET['customer_id'];
} else {
    $userId = getCurrentUserId();
}

$query = "SELECT bill_id, bill_month, previous_reading, current_reading, units_consumed, 
          rate_per_unit, total_amount, due_date, status, created_at
          FROM bills
          WHERE user_id = ?
          ORDER BY created_at DESC";

$result = executeQuery($query, array($userId));

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
