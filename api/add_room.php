<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check if user is a homeOwner
if (($_SESSION['role'] ?? '') !== 'homeOwner') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Only home owners can add rooms']);
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
$electricity = floatval($input['electricity'] ?? 0);
$gas = floatval($input['gas'] ?? 0);
$water = floatval($input['water'] ?? 0);

if (empty($name)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Room name is required']);
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
    INSERT INTO rooms
    (name, home_id, electricity_limit, gas_limit, water_limit)
    VALUES (?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "siddd",
    $name,
    $home_id,
    $electricity,
    $gas,
    $water
);
if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Room added successfully',
        'room_id' => $stmt->insert_id
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to add room: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
