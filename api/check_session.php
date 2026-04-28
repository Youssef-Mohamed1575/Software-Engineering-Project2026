<?php
session_start();
header('Content-Type: application/json');

/**
 * Session Check API
 * Returns the current session state and user information if logged in.
 */

if (isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => true,
        'loggedIn' => true,
        'user' => [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'role' => $_SESSION['role']
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

