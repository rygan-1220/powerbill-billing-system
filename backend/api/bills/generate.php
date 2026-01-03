<?php
require_once '../../config/database.php';
require_once '../../utils/session.php';

setCorsHeaders();
requireAnyRole(['admin', 'staff']);

$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$customerId = isset($input['customer_id']) ? intval($input['customer_id']) : 0;
$billMonth = isset($input['bill_month']) ? trim($input['bill_month']) : '';
$previousReading = isset($input['previous_reading']) ? floatval($input['previous_reading']) : 0;
$currentReading = isset($input['current_reading']) ? floatval($input['current_reading']) : 0;
$ratePerUnit = isset($input['rate_per_unit']) ? floatval($input['rate_per_unit']) : 0;
$dueDate = isset($input['due_date']) ? trim($input['due_date']) : '';
$adminId = getCurrentUserId();

// Validate input
if ($customerId === 0 || empty($billMonth) || $currentReading === 0 || $ratePerUnit === 0 || empty($dueDate)) {
    echo json_encode([
        'success' => false,
        'message' => 'All required fields must be filled'
    ]);
    exit;
}

// Calculate units consumed and total amount
$unitsConsumed = $currentReading - $previousReading;
$totalAmount = $unitsConsumed * $ratePerUnit;

// Insert bill
$query = "INSERT INTO bills (user_id, bill_month, previous_reading, current_reading, units_consumed, 
          rate_per_unit, total_amount, due_date, created_by, status)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

$result = executeQuery($query, array(
    $customerId, 
    $billMonth, 
    $previousReading, 
    $currentReading, 
    $unitsConsumed, 
    $ratePerUnit, 
    $totalAmount, 
    $dueDate, 
    $adminId
));

if (!$result['success']) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to generate bill',
        'error' => $result['error']
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Bill generated successfully',
    'bill_details' => [
        'units_consumed' => $unitsConsumed,
        'total_amount' => $totalAmount
    ]
]);
?>
