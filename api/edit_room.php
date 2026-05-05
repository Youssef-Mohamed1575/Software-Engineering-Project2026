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
        'message' => 'Only home owners can edit rooms'
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$room_id = intval($input['room_id'] ?? 0);
$name = $input['name'] ?? '';

$electricity = floatval($input['electricity'] ?? 0);
$gas = floatval($input['gas'] ?? 0);
$water = floatval($input['water'] ?? 0);

$home_id = intval($_SESSION['home_id'] ?? 0);

if (!$room_id || empty($name)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields'
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

$stmt = $conn->prepare("
    UPDATE rooms
    SET name = ?, electricity_limit = ?, gas_limit = ?, water_limit = ?
    WHERE id = ? AND home_id = ?
");

$stmt->bind_param(
    "sdddii",
    $name,
    $electricity,
    $gas,
    $water,
    $room_id,
    $home_id
);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Room updated successfully'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update room'
    ]);
}

$stmt->close();
$conn->close();
?>