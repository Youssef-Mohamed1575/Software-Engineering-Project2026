<?php
mysqli_report(MYSQLI_REPORT_OFF);
session_start();              // add this
header('Content-Type: application/json');  // add this

try {
    $conn = new mysqli("localhost", "root", "", "projectdb");

    if ($conn->connect_error) {
        http_response_code(500);
        die(json_encode([
            "success" => false,
            "message" => "Database connection failed"
        ]));
    }

} catch (Exception $e) {
    http_response_code(500);
    die(json_encode([
        "success" => false,
        "message" => "Database connection failed"
    ]));
}

header('Content-Type: application/json');

$dateToday = date('Y-m-d');

$query = "
    SELECT 
        SUM((total_minutes / 60) * electricity) AS total_electricity,
        SUM((total_minutes / 60) * gas) AS total_gas,
        SUM((total_minutes / 60) * water) AS total_water
    FROM devices
";

$result = $conn->query($query);

if (!$result) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Failed to calculate resource usage"
    ]);
    exit;
}

$data = $result->fetch_assoc();

$totalElectricity = $data['total_electricity'] ?? 0;
$totalGas = $data['total_gas'] ?? 0;
$totalWater = $data['total_water'] ?? 0;

$home_id = intval($_SESSION['home_id']);
$stmt = $conn->prepare("
    INSERT INTO daily_resource_usage (
        usage_date,
        home_id,
        total_electricity,
        total_gas,
        total_water
    ) VALUES (?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        total_electricity = VALUES(total_electricity),
        total_gas = VALUES(total_gas),
        total_water = VALUES(total_water)
");

$stmt->bind_param(
    "siddd",
    $dateToday,
    $home_id,
    $totalElectricity,
    $totalGas,
    $totalWater
);

if ($stmt->execute()) {

    // Reset daily tracked device usage after successful save
    $conn->query("UPDATE devices SET active_minutes = 0");

    echo json_encode([
        "success" => true,
        "message" => "Daily resource usage updated successfully",
        "date" => $dateToday,
        "totals" => [
            "electricity" => round($totalElectricity, 2),
            "gas" => round($totalGas, 2),
            "water" => round($totalWater, 2)
        ]
    ]);

} else {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Failed to save daily resource usage"
    ]);
}

$stmt->close();
$conn->close();
?>