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

$currentMonth = date('m');
$currentYear = date('Y');
$home_id = intval($_SESSION['home_id']);
$stmt = $conn->prepare("
    SELECT 
    SUM(
        CASE
            WHEN MONTH(usage_date) = ? AND YEAR(usage_date) = ?
            THEN total_electricity
            ELSE 0
        END
    ) AS total_electricity,

    SUM(
        CASE
            WHEN MONTH(usage_date) = ? AND YEAR(usage_date) = ?
            THEN total_gas
            ELSE 0
        END
    ) AS total_gas,

    SUM(
        CASE
            WHEN MONTH(usage_date) = ? AND YEAR(usage_date) = ?
            THEN total_water
            ELSE 0
        END
    ) AS total_water

FROM daily_resource_usage
WHERE home_id = ?
");
$stmt->bind_param("iiiiiii", $currentMonth, $currentYear, $currentMonth, $currentYear, $currentMonth, $currentYear   , $home_id);
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