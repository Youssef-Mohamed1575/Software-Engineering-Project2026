<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

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
$room_id = $input['room_id'] ?? null;
$electricity = $input['electricity'] ?? 0;
$gas = $input['gas'] ?? 0;
$water = $input['water'] ?? 0;


if (empty($name) || empty($type)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Device name and type are required']);
    exit;
}

mysqli_report(MYSQLI_REPORT_OFF);

try {
    $conn = @new mysqli("localhost", "root", "", "projectdb");
    if ($conn->connect_error) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}


$stmt = $conn->prepare("
    INSERT INTO devices
    (name, type, status, home_id, room_id, electricity, gas, water)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");
    $stmt->bind_param("sssiiddd", $name, $type, $status, $home_id, $room_id, $electricity, $gas, $water);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Device added successfully',
        'device_id' => $stmt->insert_id
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to add device: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
