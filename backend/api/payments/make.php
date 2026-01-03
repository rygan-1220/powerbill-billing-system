<?php
require_once '../../config/database.php';
require_once '../../utils/session.php';

setCorsHeaders();
requireRole('customer');

$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$billId = isset($input['bill_id']) ? $input['bill_id'] : '';
$amount = isset($input['amount']) ? $input['amount'] : '';
$paymentMethod = isset($input['payment_method']) ? $input['payment_method'] : '';
$cardId = isset($input['card_id']) ? trim($input['card_id']) : null;
$userId = getCurrentUserId();

// Validate input
if (empty($billId) || empty($amount) || empty($paymentMethod)) {
    echo json_encode([
        'success' => false,
        'message' => 'Bill ID, amount, and payment method are required'
    ]);
    exit;
}

// Verify bill belongs to user
$verifyQuery = "SELECT bill_id, total_amount, status FROM bills WHERE bill_id = ? AND user_id = ?";
$verifyResult = executeQuery($verifyQuery, array($billId, $userId));

if (!$verifyResult['success']) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to verify bill'
    ]);
    exit;
}

$bill = fetchOne($verifyResult['statement']);

if (!$bill) {
    echo json_encode([
        'success' => false,
        'message' => 'Bill not found or access denied'
    ]);
    exit;
}

if ($bill['status'] === 'paid') {
    echo json_encode([
        'success' => false,
        'message' => 'Bill already paid'
    ]);
    exit;
}

// Generate transaction ID
$transactionId = 'TXN' . time() . rand(1000, 9999);

// Insert payment
// card_id is optional; include in insert
if ($cardId !== null && $cardId !== '') {
    $insertQuery = "INSERT INTO payments (bill_id, user_id, amount, payment_method, transaction_id, card_id, status)
                    VALUES (?, ?, ?, ?, ?, ?, 'completed')";
    $insertParams = array($billId, $userId, $amount, $paymentMethod, $transactionId, $cardId);
} else {
    $insertQuery = "INSERT INTO payments (bill_id, user_id, amount, payment_method, transaction_id, status)
                    VALUES (?, ?, ?, ?, ?, 'completed')";
    $insertParams = array($billId, $userId, $amount, $paymentMethod, $transactionId);
}

$insertResult = executeQuery($insertQuery, $insertParams);

if (!$insertResult['success']) {
    echo json_encode([
        'success' => false,
        'message' => 'Payment failed',
        'error' => $insertResult['error']
    ]);
    exit;
}

// Update bill status
$updateQuery = "UPDATE bills SET status = 'paid' WHERE bill_id = ?";
executeQuery($updateQuery, array($billId));

echo json_encode([
    'success' => true,
    'message' => 'Payment successful',
    'transaction_id' => $transactionId
]);
?>
