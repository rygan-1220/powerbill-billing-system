<?php
// Session utility functions
session_start();

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

// Check if user has required role
function hasRole($role) {
    return isLoggedIn() && $_SESSION['role'] === $role;
}

// Check if user has one of the required roles
function hasAnyRole($roles) {
    if (!isLoggedIn()) {
        return false;
    }
    return in_array($_SESSION['role'], $roles);
}

// Get current user ID
function getCurrentUserId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

// Get current user role
function getCurrentUserRole() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}

// Get current user data
function getCurrentUser() {
    return [
        'user_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null,
        'username' => isset($_SESSION['username']) ? $_SESSION['username'] : null,
        'role' => isset($_SESSION['role']) ? $_SESSION['role'] : null,
        'full_name' => isset($_SESSION['full_name']) ? $_SESSION['full_name'] : null,
        'email' => isset($_SESSION['email']) ? $_SESSION['email'] : null
    ];
}

// Require login
function requireLogin() {
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized. Please login.'
        ]);
        exit;
    }
}

// Require specific role
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Access denied. Insufficient permissions.'
        ]);
        exit;
    }
}

// Require one of multiple roles
function requireAnyRole($roles) {
    requireLogin();
    if (!hasAnyRole($roles)) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Access denied. Insufficient permissions.'
        ]);
        exit;
    }
}

// Set CORS headers
function setCorsHeaders() {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? 'http://localhost';
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Credentials: true');
    header('Content-Type: application/json');
    
    // Handle preflight requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}
?>
