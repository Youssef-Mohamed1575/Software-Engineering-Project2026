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

$query = "
SELECT 
    rooms.id,
    rooms.name,
    rooms.electricity_limit,
    rooms.gas_limit,
    rooms.water_limit,

    SUM((devices.total_minutes / 60) * devices.electricity) AS total_electricity,
    SUM((devices.total_minutes / 60) * devices.gas) AS total_gas,
    SUM((devices.total_minutes / 60) * devices.water) AS total_water

FROM rooms
LEFT JOIN devices ON rooms.id = devices.room_id
WHERE rooms.home_id = ?
GROUP BY rooms.id
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $home_id);
$stmt->execute();
$result = $stmt->get_result();

$alerts = [];

while ($room = $result->fetch_assoc()) {

    if ($room['electricity_limit'] > 0 && $room['total_electricity'] > $room['electricity_limit']) {
        $alerts[] = [
            "room_id" => $room['id'],
            "room_name" => $room['name'],
            "resource" => "electricity",
            "total" => $room['total_electricity'],
            "limit" => $room['electricity_limit'],
            "unit" => "kWh"
        ];
    }

    if ($room['gas_limit'] > 0 && $room['total_gas'] > $room['gas_limit']) {
        $alerts[] = [
            "room_id" => $room['id'],
            "room_name" => $room['name'],
            "resource" => "gas",
            "total" => $room['total_gas'],
            "limit" => $room['gas_limit'],
            "unit" => "m³"
        ];
    }

    if ($room['water_limit'] > 0 && $room['total_water'] > $room['water_limit']) {
        $alerts[] = [
            "room_id" => $room['id'],
            "room_name" => $room['name'],
            "resource" => "water",
            "total" => $room['total_water'],
            "limit" => $room['water_limit'],
            "unit" => "L"
        ];
    }
}

echo json_encode([
    "success" => true,
    "alerts" => $alerts
]);

$stmt->close();
$conn->close();
?>