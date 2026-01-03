<?php
// Database Configuration for MS SQL Server
// Using Microsoft's SQLSRV driver
// Reference: https://learn.microsoft.com/en-us/sql/connect/php/step-3-proof-of-concept-connecting-to-sql-using-php

// Check if SQLSRV extension is loaded
if (!extension_loaded('sqlsrv')) {
    die(json_encode([
        'success' => false,
        'message' => 'SQLSRV extension is not loaded. Please install/enable the PHP SQL Server driver.',
        'error' => 'Extension sqlsrv not found'
    ]));
}

// Database credentials
$serverName = "localhost";  
$database = "ElectricityBilling";
$username = "web";
$password = "Web#123";

// Connection options
$connectionOptions = array(
    "Database" => $database,
    "UID" => $username,
    "PWD" => $password,
    "CharacterSet" => "UTF-8",
    "ReturnDatesAsStrings" => true,
    "TrustServerCertificate" => true,  // Required for local/self-signed certificates
);

// IMPORTANT: Configure warnings BEFORE connecting
// This prevents informational messages from being treated as errors
sqlsrv_configure('WarningsReturnAsErrors', 0);

// Helper function to format errors
function FormatErrors($errors) {
    if (empty($errors)) {
        return "Unknown error";
    }
    
    $errorMessages = [];
    foreach ($errors as $error) {
        $errorMessages[] = sprintf(
            "[SQLSTATE: %s] [Code: %s] %s",
            $error['SQLSTATE'] ?? 'N/A',
            $error['code'] ?? 'N/A',
            $error['message'] ?? 'Unknown error'
        );
    }
    return implode(" | ", $errorMessages);
}

// Create connection
$conn = sqlsrv_connect($serverName, $connectionOptions);

// Check for connection errors
if ($conn === false) {
    $errors = sqlsrv_errors(SQLSRV_ERR_ERRORS);  // Get only actual errors, not warnings
    
    if (php_sapi_name() !== 'cli') {
        header('Content-Type: application/json');
    }
    
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed',
        'error' => FormatErrors($errors)
    ]));
}

// Function to execute query with parameters
function executeQuery($query, $params = array()) {
    global $conn;
    
    $stmt = sqlsrv_query($conn, $query, $params);
    
    if ($stmt === false) {
        return [
            'success' => false,
            'error' => FormatErrors(sqlsrv_errors())
        ];
    }
    
    return [
        'success' => true,
        'statement' => $stmt
    ];
}

// Function to fetch all results as associative array
function fetchAll($stmt) {
    $results = array();
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $results[] = $row;
    }
    sqlsrv_free_stmt($stmt);
    return $results;
}

// Function to fetch single row
function fetchOne($stmt) {
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    sqlsrv_free_stmt($stmt);
    return $row;
}

// Function to get last inserted ID (use SCOPE_IDENTITY())
function getLastInsertId() {
    global $conn;
    $query = "SELECT SCOPE_IDENTITY() AS id";
    $stmt = sqlsrv_query($conn, $query);
    
    if ($stmt === false) {
        return null;
    }
    
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    sqlsrv_free_stmt($stmt);
    return $row ? $row['id'] : null;
}

// Function to get number of affected rows
function getRowsAffected($stmt) {
    return sqlsrv_rows_affected($stmt);
}

// Function to close connection (call when done)
function closeConnection() {
    global $conn;
    if ($conn) {
        sqlsrv_close($conn);
        $conn = null;
    }
}
?>
