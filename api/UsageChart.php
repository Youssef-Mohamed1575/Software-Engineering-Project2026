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
Get last 7 days from daily_resource_usage
*/
$query = "
    SELECT 
        usage_date,
        total_electricity,
        total_gas,
        total_water
    FROM daily_resource_usage
    ORDER BY usage_date ASC
    LIMIT 7
";

$result = $conn->query($query);

$labels = [];
$electricity = [];
$gas = [];
$water = [];

while ($row = $result->fetch_assoc()) {
    $labels[] = date("D", strtotime($row['usage_date']));
    $electricity[] = round($row['total_electricity'], 2);
    $gas[] = round($row['total_gas'], 2);
    $water[] = round($row['total_water'], 2);
}

echo json_encode([
    "success" => true,
    "labels" => $labels,
    "electricity" => $electricity,
    "gas" => $gas,
    "water" => $water
]);

$conn->close();
?>