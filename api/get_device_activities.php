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
        'message' => 'Only homeowners can access the activity log'
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
    SELECT id, device_name, activity, done_by, timestamp 
    FROM device_activities 
    WHERE home_id = ? 
    ORDER BY timestamp DESC
");
$stmt->bind_param("i", $home_id);
$stmt->execute();
$result = $stmt->get_result();

$activities = [];
while ($row = $result->fetch_assoc()) {
    $activities[] = $row;
}

echo json_encode([
    'success' => true,
    'activities' => $activities
]);

$stmt->close();
$conn->close();
?>
