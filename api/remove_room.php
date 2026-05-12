<?php
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (($_SESSION['role'] ?? '') !== 'homeOwner') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Only home owners can remove rooms']);
    exit;
}

$home_id = $_SESSION['home_id'] ?? null;
if (!$home_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No home associated with this user']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['room_id'])) {
    echo json_encode(["success" => false, "message" => "Room ID missing"]);
    exit;
}

$room_id = intval($data['room_id']);
$home_id = $_SESSION['home_id'];

$conn = new mysqli("localhost", "root", "", "projectdb");

$conn->query("DELETE FROM devices WHERE room_id = $room_id AND home_id = $home_id");

$conn->query("DELETE FROM rooms WHERE id = $room_id AND home_id = $home_id");

echo json_encode(["success" => true]);
?>