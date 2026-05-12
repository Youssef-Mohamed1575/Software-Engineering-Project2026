<?php
session_start();

if (!isset($_SESSION['home_id'])) {
    die("Unauthorized");
}

$conn = new mysqli("localhost", "root", "", "projectdb");

if ($conn->connect_error) {
    die("Database connection failed.");
}

$home_id = intval($_SESSION['home_id']);


$summaryQuery = "
    SELECT 
        COUNT(DISTINCT r.id) AS total_rooms,
        COUNT(DISTINCT d.id) AS total_devices,
        SUM(d.electricity * d.total_minutes) AS total_electricity,
        SUM(d.gas * d.total_minutes) AS total_gas,
        SUM(d.water * d.total_minutes) AS total_water
    FROM rooms r
    LEFT JOIN devices d ON r.id = d.room_id
    WHERE r.home_id = $home_id
";

$summary = $conn->query($summaryQuery)->fetch_assoc();


$roomsQuery = "SELECT * FROM rooms WHERE home_id = $home_id";
$roomsResult = $conn->query($roomsQuery);
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Household Report</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        @media print {
            #printBtn {
                display: none;
            }
        }
    </style>
</head>
<body class="bg-slate-100 p-10">

    <div class="max-w-6xl mx-auto bg-white shadow-lg rounded-lg p-10">

        <div class="flex justify-between items-center mb-8">
            <h1 class="text-4xl font-bold">Household Resource Report</h1>

            <button id="printBtn"
                onclick="window.print()"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-bold">
                Download PDF
            </button>
        </div>


        <div class="mb-10">
            <h2 class="text-2xl font-bold mb-4">Household Summary</h2>

            <div class="grid grid-cols-2 gap-4 text-lg">
                <p>Total Rooms: <?= $summary['total_rooms'] ?></p>
                <p>Total Devices: <?= $summary['total_devices'] ?></p>
                <p>Total Electricity Usage: <?= round($summary['total_electricity'], 2) ?> kWh</p>
                <p>Total Gas Usage: <?= round($summary['total_gas'], 2) ?> m³</p>
                <p>Total Water Usage: <?= round($summary['total_water'], 2) ?> L</p>
            </div>
        </div>

        <?php while ($room = $roomsResult->fetch_assoc()): ?>

            <?php
            $room_id = intval($room['id']);

            $devicesQuery = "SELECT * FROM devices WHERE room_id = $room_id";
            $devicesResult = $conn->query($devicesQuery);

            $roomElectricity = 0;
            $roomGas = 0;
            $roomWater = 0;
            ?>

            <div class="mb-12 border-t pt-8">

                <h2 class="text-2xl font-bold mb-2">
                    Room: <?= htmlspecialchars($room['name']) ?>
                </h2>

                <?php
                while ($deviceCalc = $devicesResult->fetch_assoc()) {
                    $roomElectricity += $deviceCalc['electricity'] * $deviceCalc['total_minutes'];
                    $roomGas += $deviceCalc['gas'] * $deviceCalc['total_minutes'];
                    $roomWater += $deviceCalc['water'] * $deviceCalc['total_minutes'];
                }

                $devicesResult = $conn->query($devicesQuery);
                ?>

                <div class="mb-4">
                    <p>Electricity Limit: <?= $room['electricity_limit'] ?> kWh | Usage: <?= round($roomElectricity, 2) ?> kWh</p>
                    <p>Gas Limit: <?= $room['gas_limit'] ?> m³ | Usage: <?= round($roomGas, 2) ?> m³</p>
                    <p>Water Limit: <?= $room['water_limit'] ?> L | Usage: <?= round($roomWater, 2) ?> L</p>
                </div>

                <table class="w-full border-collapse border border-slate-300">
                    <thead class="bg-slate-200">
                        <tr>
                            <th class="border p-2">Device Name</th>
                            <th class="border p-2">Type</th>
                            <th class="border p-2">Status</th>
                            <th class="border p-2">Electricity</th>
                            <th class="border p-2">Gas</th>
                            <th class="border p-2">Water</th>
                            <th class="border p-2">Total Minutes</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php while ($device = $devicesResult->fetch_assoc()): ?>
                            <tr>
                                <td class="border p-2"><?= htmlspecialchars($device['name']) ?></td>
                                <td class="border p-2"><?= htmlspecialchars($device['type']) ?></td>
                                <td class="border p-2"><?= htmlspecialchars($device['status']) ?></td>
                                <td class="border p-2"><?= round($device['electricity'] * $device['total_minutes'], 2) ?></td>
                                <td class="border p-2"><?= round($device['gas'] * $device['total_minutes'], 2) ?></td>
                                <td class="border p-2"><?= round($device['water'] * $device['total_minutes'], 2) ?></td>
                                <td class="border p-2"><?= $device['total_minutes'] ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        <?php endwhile; ?>

    </div>

</body>
</html>

<?php
$conn->close();
?>