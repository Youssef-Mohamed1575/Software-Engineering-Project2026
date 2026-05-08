<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
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
---------------------------------
CONFIGURABLE TARIFF VARIABLES
---------------------------------
Adjust these anytime
*/

$electricityTariff = 1.55; // EGP per kWh
$gasTariff = 4;         // EGP per m³
$waterTariff = 0.08;       // EGP per liter

/*
---------------------------------
CURRENT TOTAL USAGE
---------------------------------
*/

$stmt = $conn->prepare("
    SELECT 
        SUM((total_minutes / 60) * electricity) AS electricity_usage,
        SUM((total_minutes / 60) * gas) AS gas_usage,
        SUM((total_minutes / 60) * water) AS water_usage
    FROM devices
    WHERE home_id = ?
");

$stmt->bind_param("i", $home_id);
$stmt->execute();
$current = $stmt->get_result()->fetch_assoc();

$currentElectricity = $current['electricity_usage'] ?? 0;
$currentGas = $current['gas_usage'] ?? 0;
$currentWater = $current['water_usage'] ?? 0;

/*
---------------------------------
CURRENT BILL
---------------------------------
*/

$currentBill =
    ($currentElectricity * $electricityTariff) +
    ($currentGas * $gasTariff) +
    ($currentWater * $waterTariff);

/*
---------------------------------
MONTH PROJECTION
---------------------------------
*/

$currentDay = date('j');
$totalDaysInMonth = date('t');

$projectedBill = ($currentDay > 0)
    ? ($currentBill / $currentDay) * $totalDaysInMonth
    : $currentBill;

/*
---------------------------------
LAST MONTH ESTIMATE
---------------------------------
Simple placeholder:
Could later use archived monthly data
*/

$lastMonthBill = $currentBill * 1.5;

/*
---------------------------------
CHANGE %
---------------------------------
*/

$changePercent = ($lastMonthBill > 0)
    ? (($currentBill - $lastMonthBill) / $lastMonthBill) * 100
    : 0;

/*
---------------------------------
RESOURCE DISTRIBUTION
---------------------------------
*/

$totalResourceCost =
    ($currentElectricity * $electricityTariff) +
    ($currentGas * $gasTariff) +
    ($currentWater * $waterTariff);

$electricityPercent = ($totalResourceCost > 0)
    ? (($currentElectricity * $electricityTariff) / $totalResourceCost) * 100
    : 0;

$gasPercent = ($totalResourceCost > 0)
    ? (($currentGas * $gasTariff) / $totalResourceCost) * 100
    : 0;

$waterPercent = ($totalResourceCost > 0)
    ? (($currentWater * $waterTariff) / $totalResourceCost) * 100
    : 0;

/*
---------------------------------
OUTPUT
---------------------------------
*/

echo json_encode([
    "success" => true,
    "tariffs" => [
        "electricity" => $electricityTariff,
        "gas" => $gasTariff,
        "water" => $waterTariff
    ],
    "usage" => [
        "electricity" => round($currentElectricity, 2),
        "gas" => round($currentGas, 2),
        "water" => round($currentWater, 2)
    ],
    "billing" => [
        "current_bill" => round($currentBill, 2),
        "projected_month_bill" => round($projectedBill, 2),
        "last_month_bill" => round($lastMonthBill, 2),
        "change_percent" => round($changePercent, 2)
    ],
    "distribution" => [
        "electricity" => round($electricityPercent, 2),
        "gas" => round($gasPercent, 2),
        "water" => round($waterPercent, 2)
    ]
]);

$stmt->close();
$conn->close();
?>