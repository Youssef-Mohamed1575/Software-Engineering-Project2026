<?php
session_start();
header('Content-Type: application/json');

if(($_SESSION['role'] ?? '') !== 'homeOwner'){
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$home_id = $_SESSION['home_id'] ?? null;

$input = json_decode(file_get_contents('php://input'), true);
$username = $input['username'] ?? '';
$password = $input['password'] ?? '';
$role = $input['role'] ?? '';

if (empty($username) || empty($password) || empty($role)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Username, password, and role are required']);
    exit;
}

$valid_roles = ['homeOwner', 'homeAdult', 'homeKid', 'guest'];
if (!in_array($role, $valid_roles)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid role']);
    exit;
}

$conn = new mysqli("localhost", "root", "", "projectdb");
if($conn->connect_error){
    http_response_code(500);
    echo json_encode(["success"=> false,"message"=> $conn->connect_error]);
    exit();
}

$check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$check_stmt->bind_param("s", $username);
$check_stmt->execute();
if ($check_stmt->get_result()->num_rows > 0) {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'Username already exists']);
    $check_stmt->close();
    $conn->close();
    exit;
}
$check_stmt->close();

$stmt = $conn->prepare("INSERT INTO users (username, password, role, home_id) VALUES (?, ?, ?, ?)");
$stmt->bind_param("sssi", $username, $password, $role, $home_id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'User added successfully',
        'user_id' => $stmt->insert_id
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to add user: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>