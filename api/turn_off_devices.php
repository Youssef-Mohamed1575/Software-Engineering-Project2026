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

$home_id = intval($_SESSION['home_id'] ?? 0);

if (!$home_id) {
    http_response_code(400);
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
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit;
}

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
    total_minutes = total_minutes +
        CASE
            WHEN last_activated_at IS NOT NULL
            THEN TIMESTAMPDIFF(MINUTE, last_activated_at, NOW())
            ELSE 0
        END,
    last_activated_at = NULL
WHERE home_id = ?
    ");

    $stmt->bind_param("i", $home_id);

}
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

        total_minutes = total_minutes +
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
            total_minutes = total_minutes +
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
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid type specified'
    ]);
    exit;
}
if ($stmt->execute()) {
    // Log activity
    $bulk_name = "All Devices";
    if ($type === "light") $bulk_name = "All Lights";
    else if ($type === "heater") $bulk_name = "All Heaters";
    else if ($type === "ac") $bulk_name = "All ACs";

    $activity = "Turned OFF";
    $done_by = $_SESSION['username'] ?? 'Unknown';

    $log_stmt = $conn->prepare("INSERT INTO device_activities (device_name, activity, done_by, home_id) VALUES (?, ?, ?, ?)");
    $log_stmt->bind_param("sssi", $bulk_name, $activity, $done_by, $home_id);
    $log_stmt->execute();
    $log_stmt->close();

    echo json_encode([
        'success' => true,
        'message' => ucfirst($type) . ' turned off successfully'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update devices'
    ]);
}

$stmt->close();
$conn->close();
?>