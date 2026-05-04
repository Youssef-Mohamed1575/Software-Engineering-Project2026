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
        'message' => 'Only home owners can edit devices'
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$device_id = intval($input['device_id'] ?? 0);
$name = $input['name'] ?? '';
$type = $input['type'] ?? '';
$status = $input['status'] ?? 'off';
$electricity = floatval($input['electricity'] ?? 0);
$gas = floatval($input['gas'] ?? 0);
$water = floatval($input['water'] ?? 0);

$home_id = intval($_SESSION['home_id'] ?? 0);

if (!$device_id || empty($name) || empty($type)) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields'
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
    UPDATE devices
SET name = ?, type = ?, electricity = ?, gas = ?, water = ?
WHERE id = ? AND home_id = ?
");

$stmt->bind_param(
    "ssdddii",
    $name,
    $type,
    $electricity,
    $gas,
    $water,
    $device_id,
    $home_id
);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Device updated successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update device'
    ]);
}

$stmt->close();
$conn->close();
?>