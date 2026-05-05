<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$device_id = intval($input['device_id'] ?? 0);
$new_status = $input['status'] ?? 'off';

$home_id = intval($_SESSION['home_id'] ?? 0);

if (!$device_id) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid device'
    ]);
    exit;
}

$conn = new mysqli("localhost", "root", "", "projectdb");

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit;
}

// TURNING ON
if ($new_status === "on") {

    $stmt = $conn->prepare("
        UPDATE devices
        SET status = 'on',
            last_activated_at = NOW()
        WHERE id = ? AND home_id = ?
    ");

    $stmt->bind_param("ii", $device_id, $home_id);
}

// TURNING OFF
else {

// REPLACE OFF query with this safer version:

$stmt = $conn->prepare("
    UPDATE devices
    SET
        status = 'off',
        active_minutes = active_minutes +
            CASE
                WHEN last_activated_at IS NOT NULL
                THEN TIMESTAMPDIFF(MINUTE, last_activated_at, NOW())
                ELSE 0
            END,
        last_activated_at = NULL
    WHERE id = ? AND home_id = ?
");
$stmt->bind_param("ii", $device_id, $home_id);
}

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Device status updated'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update status'
    ]);
}

$stmt->close();
$conn->close();
?>