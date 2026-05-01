<?php
$conn = new mysqli("localhost", "root", "");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->query("CREATE DATABASE IF NOT EXISTS projectdb");
$conn->select_db("projectdb");

$createTableQuery = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL,
    home_id INT DEFAULT 1
)";

// Ensure home_id exists if table was already created
$checkColumn = $conn->query("SHOW COLUMNS FROM users LIKE 'home_id'");
if ($checkColumn->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD COLUMN home_id INT DEFAULT 1");
}

if ($conn->query($createTableQuery)) {
    echo "Table 'users' updated or created.<br>";
}

// Create devices table
$createDevicesTableQuery = "CREATE TABLE IF NOT EXISTS devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(50) NOT NULL,
    status VARCHAR(20) DEFAULT 'off',
    home_id INT NOT NULL,
    room_id INT DEFAULT NULL
)";

if ($conn->query($createDevicesTableQuery)) {
    echo "Table 'devices' created or already exists.<br>";
} else {
    echo "Error creating table 'devices': " . $conn->error . "<br>";
}

$checkEmpty = $conn->query("SELECT COUNT(*) as count FROM users");
$row = $checkEmpty->fetch_assoc();

if ($row['count'] == 0) {
    $defaultPassword = 'password123';
    $users = [
        ['owner', $defaultPassword, 'homeOwner'],
        ['adult', $defaultPassword, 'homeAdult'],
        ['kid', $defaultPassword, 'homeKid'],
        ['guest', $defaultPassword, 'guest']
    ];

    $stmt = $conn->prepare("INSERT INTO users (username, password, role, home_id) VALUES (?, ?, ?, 1)");
    foreach ($users as $user) {
        $stmt->bind_param("sss", $user[0], $user[1], $user[2]);
        $stmt->execute();
    }
    echo "Default users seeded successfully with password: <b>$defaultPassword</b><br>";
}

echo "Setup complete!";
$conn->close();
?>