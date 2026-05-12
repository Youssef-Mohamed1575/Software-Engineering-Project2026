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
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid device or home'
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

$device_name = "Unknown Device";
$name_stmt = $conn->prepare("SELECT name FROM devices WHERE id = ? AND home_id = ?");
$name_stmt->bind_param("ii", $device_id, $home_id);
$name_stmt->execute();
$name_result = $name_stmt->get_result();
if ($row = $name_result->fetch_assoc()) {
    $device_name = $row['name'];
}
$name_stmt->close();

$stmt = $conn->prepare("
    DELETE FROM devices
    WHERE id = ? AND home_id = ?
");

$stmt->bind_param("ii", $device_id, $home_id);

if ($stmt->execute()) {
    $activity = "Removed Device";
    $done_by = $_SESSION['username'] ?? 'Unknown';

    $log_stmt = $conn->prepare("INSERT INTO device_activities (device_name, activity, done_by, home_id) VALUES (?, ?, ?, ?)");
    $log_stmt->bind_param("sssi", $device_name, $activity, $done_by, $home_id);
    $log_stmt->execute();
    $log_stmt->close();

    echo json_encode([
        'success' => true,
        'message' => 'Device removed successfully'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to remove device'
    ]);
}

$stmt->close();
$conn->close();
?>