<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$home_id = $_SESSION['home_id'] ?? null;

if (!$home_id) {
    echo json_encode(['success' => false, 'message' => 'No home associated with this user']);
    exit;
}

$conn = new mysqli("localhost", "root", "", "projectdb");

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$stmt = $conn->prepare("SELECT id, name, type, status, room_id FROM devices WHERE home_id = ?");
$stmt->bind_param("i", $home_id);
$stmt->execute();
$result = $stmt->get_result();

$devices = [];
while ($row = $result->fetch_assoc()) {
    $devices[] = $row;
}

echo json_encode([
    'success' => true,
    'devices' => $devices
]);

$stmt->close();
$conn->close();
?>
