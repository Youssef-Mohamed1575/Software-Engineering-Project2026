<?php
session_start();

header('Content-Type: application/json');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

$_SESSION = [];
session_unset();

// Delete session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        '/',
        '',
        false,
        true
    );
}

// Force destroy session file
session_destroy();

echo json_encode([
    'success' => true,
    'message' => 'Logged out successfully'
]);
exit;
?>