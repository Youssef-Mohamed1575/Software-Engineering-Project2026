<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => true,
        'loggedIn' => true,
        'user' => [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'role' => $_SESSION['role'],
            'home_id' => $_SESSION['home_id'] ?? null
        ]
    ]);
} else {
    echo json_encode([
        'success' => true,
        'loggedIn' => false,
        'user' => null,
        'message' => 'No active session'
    ]);
}
?>