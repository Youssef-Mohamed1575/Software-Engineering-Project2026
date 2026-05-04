<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
    exit;
}

if (($_SESSION['role'] ?? '') !== 'homeOwner') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Only home owners can remove devices'
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$device_id = intval($input['device_id'] ?? 0);
$home_id = intval($_SESSION['home_id'] ?? 0);

if (!$device_id || !$home_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid device or home'
    ]);
    exit;
}

$conn = new mysqli("localhost", "root", "", "projectdb");

if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit;
}

$stmt = $conn->prepare("
    DELETE FROM devices
    WHERE id = ? AND home_id = ?
");

$stmt->bind_param("ii", $device_id, $home_id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Device removed successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to remove device'
    ]);
}

$stmt->close();
$conn->close();
?>