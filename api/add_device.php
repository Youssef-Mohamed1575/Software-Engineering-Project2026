<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check if user is a homeOwner (based on requirements)
if (($_SESSION['role'] ?? '') !== 'homeOwner') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Only home owners can add devices']);
    exit;
}

$home_id = $_SESSION['home_id'] ?? null;
if (!$home_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No home associated with this user']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$name = $input['name'] ?? '';
$type = $input['type'] ?? '';
$status = $input['status'] ?? 'off';

if (empty($name) || empty($type)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Device name and type are required']);
    exit;
}

$conn = new mysqli("localhost", "root", "", "projectdb");

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO devices (name, type, status, home_id) VALUES (?, ?, ?, ?)");
$stmt->bind_param("sssi", $name, $type, $status, $home_id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Device added successfully',
        'device_id' => $stmt->insert_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add device: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
