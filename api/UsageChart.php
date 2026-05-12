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



$home_id = intval($_SESSION['home_id']);

$rows = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $rows[$date] = [
        'usage_date'        => $date,
        'total_electricity' => 0,
        'total_gas'         => 0,
        'total_water'       => 0
    ];
}

$stmt = $conn->prepare("
    SELECT usage_date, total_electricity, total_gas, total_water
    FROM daily_resource_usage
    WHERE home_id = ?
    AND usage_date >= CURDATE() - INTERVAL 6 DAY
    ORDER BY usage_date ASC
");
$stmt->bind_param("i", $home_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $rows[$row['usage_date']] = $row;
}

$labels = [];
$electricity = [];
$gas = [];
$water = [];

foreach ($rows as $row) {
    $labels[]      = date("D", strtotime($row['usage_date']));
    $electricity[] = round($row['total_electricity'], 2);
    $gas[]         = round($row['total_gas'], 2);
    $water[]       = round($row['total_water'], 2);
}

echo json_encode([
    "success" => true,
    "labels" => $labels,
    "electricity" => $electricity,
    "gas" => $gas,
    "water" => $water
]);

$stmt->close();
$conn->close();
?>
