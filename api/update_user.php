<?php
session_start();
header('Content-Type: application/json');

if (($_SESSION['role'] ?? '') !== 'homeOwner') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$home_id = $_SESSION['home_id'] ?? null;
if (!$home_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No home associated with this user']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$user_id = $input['user_id'] ?? null;
$username = $input['username'] ?? '';
$password = $input['password'] ?? '';
$role = $input['role'] ?? '';

if (!$user_id || empty($username) || empty($role)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'User ID, username, and role are required']);
    exit;
}

$valid_roles = ['homeOwner', 'homeAdult', 'homeKid', 'guest'];
if (!in_array($role, $valid_roles)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid role']);
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

$check_stmt = $conn->prepare("SELECT id FROM users WHERE id = ? AND home_id = ?");
$check_stmt->bind_param("ii", $user_id, $home_id);
$check_stmt->execute();
if ($check_stmt->get_result()->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'User not found or unauthorized']);
    $check_stmt->close();
    $conn->close();
    exit;
}
$check_stmt->close();

$username_stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
$username_stmt->bind_param("si", $username, $user_id);
$username_stmt->execute();
if ($username_stmt->get_result()->num_rows > 0) {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'Username already exists']);
    $username_stmt->close();
    $conn->close();
    exit;
}
$username_stmt->close();

if (!empty($password)) {
    $stmt = $conn->prepare("UPDATE users SET username = ?, password = ?, role = ? WHERE id = ?");
    $stmt->bind_param("sssi", $username, $password, $role, $user_id);
} else {
    $stmt = $conn->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
    $stmt->bind_param("ssi", $username, $role, $user_id);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'User updated successfully']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to update user: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
