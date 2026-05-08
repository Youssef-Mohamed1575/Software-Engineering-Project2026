<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['home_id'])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized"
    ]);
    exit;
}

$home_id = intval($_SESSION['home_id']);

$conn = new mysqli("localhost", "root", "", "projectdb");

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed"
    ]);
    exit;
}

/*
Formula:
(total_minutes / 60) * resource_rate
*/

$stmt = $conn->prepare("
    SELECT 
        SUM((total_minutes / 60) * electricity) AS total_electricity,
        SUM((total_minutes / 60) * gas) AS total_gas,
        SUM((total_minutes / 60) * water) AS total_water
    FROM devices
    WHERE home_id = ?
");

$stmt->bind_param("i", $home_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

echo json_encode([
    "success" => true,
    "usage" => [
        "electricity" => round($data['total_electricity'] ?? 0, 2),
        "gas" => round($data['total_gas'] ?? 0, 2),
        "water" => round($data['total_water'] ?? 0, 2)
    ]
]);

$stmt->close();
$conn->close();
?>