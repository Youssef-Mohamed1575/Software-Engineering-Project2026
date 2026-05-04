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

$home_id = intval($_SESSION['home_id'] ?? 0);

if (!$home_id) {
    echo json_encode([
        'success' => false,
        'message' => 'No home associated with this user'
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$type = strtolower(trim($input['type'] ?? 'devices'));

$conn = new mysqli("localhost", "root", "", "projectdb");

if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit;
}

// Device type filters
if ($type === "devices") {

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
        WHERE home_id = ?
    ");

    $stmt->bind_param("i", $home_id);

}// LIGHT
else if ($type === "light") {

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
        WHERE home_id = ? AND LOWER(type) = ?
    ");

    $stmt->bind_param("is", $home_id, $type);
}

// HEATER
else if ($type === "heater") {

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
        WHERE home_id = ? AND LOWER(type) = ?
    ");

    $stmt->bind_param("is", $home_id, $type);
}

// AC
else if ($type === "ac") {

    $stmt = $conn->prepare("
        UPDATE devices
        SET
            status = 'off',
            active_minutes = active_minutes +
                CASE
                    WHEN last_activated_at IS NOT NULL
                    THEN TIMESTAMPDIFF(MINUTE, last_activated_at, NOW())
                    ELSE 1
                END,
            last_activated_at = NULL
        WHERE home_id = ? AND LOWER(type) = ?
    ");

    $stmt->bind_param("is", $home_id, $type);
}


else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid type specified'
    ]);
    exit;
}
if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => ucfirst($type) . ' turned off successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update devices'
    ]);
}

$stmt->close();
$conn->close();
?>