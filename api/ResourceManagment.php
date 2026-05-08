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

$currentMonth = date('m');
$currentYear = date('Y');

$lastMonth = date('m', strtotime('-1 month'));
$lastMonthYear = date('Y', strtotime('-1 month'));

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
        SUM(
            CASE 
                WHEN MONTH(usage_date) = ? AND YEAR(usage_date) = ?
                THEN total_electricity
                ELSE 0
            END
        ) AS current_electricity,

        SUM(
            CASE 
                WHEN MONTH(usage_date) = ? AND YEAR(usage_date) = ?
                THEN total_gas
                ELSE 0
            END
        ) AS current_gas,

        SUM(
            CASE 
                WHEN MONTH(usage_date) = ? AND YEAR(usage_date) = ?
                THEN total_water
                ELSE 0
            END
        ) AS current_water,

        SUM(
            CASE 
                WHEN MONTH(usage_date) = ? AND YEAR(usage_date) = ?
                THEN total_electricity
                ELSE 0
            END
        ) AS last_electricity,

        SUM(
            CASE 
                WHEN MONTH(usage_date) = ? AND YEAR(usage_date) = ?
                THEN total_gas
                ELSE 0
            END
        ) AS last_gas,

        SUM(
            CASE 
                WHEN MONTH(usage_date) = ? AND YEAR(usage_date) = ?
                THEN total_water
                ELSE 0
            END
        ) AS last_water

    FROM daily_resource_usage
    WHERE home_id = ?
");

$home_id = intval($_SESSION['home_id']);
$stmt->bind_param(
    "iiiiiiiiiiiii",
    $currentMonth,
    $currentYear,
    $currentMonth,
    $currentYear,
    $currentMonth,
    $currentYear,
    $lastMonth,
    $lastMonthYear,
    $lastMonth,
    $lastMonthYear,
    $lastMonth,
    $lastMonthYear,
    $home_id
);

$stmt->execute();
$current = $stmt->get_result()->fetch_assoc();

$currentElectricity = $current['current_electricity'] ?? 0;
$currentGas = $current['current_gas'] ?? 0;
$currentWater = $current['current_water'] ?? 0;

$lastElectricity = $current['last_electricity'] ?? 0;
$lastGas = $current['last_gas'] ?? 0;
$lastWater = $current['last_water'] ?? 0;
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

$lastMonthBill =
    ($lastElectricity * $electricityTariff) +
    ($lastGas * $gasTariff) +
    ($lastWater * $waterTariff);

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