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

$conn = new mysqli("localhost", "root", "", "projectdb");

if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit;
}

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total_devices
    FROM devices
    WHERE home_id = ?
");

$stmt->bind_param("i", $home_id);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode([
    'success' => true,
    'total_devices' => intval($row['total_devices'])
]);

$stmt->close();
$conn->close();
?>