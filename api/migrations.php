<?php
mysqli_report(MYSQLI_REPORT_OFF);

try {
    $conn = @new mysqli("localhost", "root", "");
    if ($conn->connect_error) {
        http_response_code(500);
        die("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    http_response_code(500);
    die("Connection failed: " . $e->getMessage());
}


$conn->query("CREATE DATABASE IF NOT EXISTS projectdb");
$conn->select_db("projectdb");

// Create homes table
$createHomesTableQuery = "CREATE TABLE IF NOT EXISTS homes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    owner_id INT
)";

if ($conn->query($createHomesTableQuery)) {
    echo "Table 'homes' created or already exists.<br>";
} else {
    http_response_code(500);
    echo "Error creating table 'homes': " . $conn->error . "<br>";
}

$createTableQuery = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL,
    home_id INT,
    FOREIGN KEY (home_id) REFERENCES homes(id)
)";

if ($conn->query($createTableQuery)) {
    echo "Table 'users' updated or created.<br>";
} else {
    http_response_code(500);
    echo "Error creating table 'users': " . $conn->error . "<br>";
}

// Create rooms table
$createRoomsTableQuery = "CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    home_id INT,
    FOREIGN KEY (home_id) REFERENCES homes(id)
)";

if ($conn->query($createRoomsTableQuery)) {
    echo "Table 'rooms' created or already exists.<br>";
} else {
    http_response_code(500);
    echo "Error creating table 'rooms': " . $conn->error . "<br>";
}

// Create devices table
$createDevicesTableQuery = "CREATE TABLE IF NOT EXISTS devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(50) NOT NULL,
    status VARCHAR(20) DEFAULT 'off',
    electricity DECIMAL(10,2) DEFAULT 0,
    gas DECIMAL(10,2) DEFAULT 0,
    water DECIMAL(10,2) DEFAULT 0,
    active_minutes INT DEFAULT 0,
    last_activated_at DATETIME DEFAULT NULL
    home_id INT,
    room_id INT DEFAULT NULL,
    FOREIGN KEY (home_id) REFERENCES homes(id),
    FOREIGN KEY (room_id) REFERENCES rooms(id)
)";

if ($conn->query($createDevicesTableQuery)) {
    echo "Table 'devices' created or already exists.<br>";
} else {
    http_response_code(500);
    echo "Error creating table 'devices': " . $conn->error . "<br>";
}

// Seed users
$checkEmpty = $conn->query("SELECT COUNT(*) as count FROM users");
if ($checkEmpty) {
    $row = $checkEmpty->fetch_assoc();

    if ($row['count'] == 0) {
    $defaultPassword = 'password123';
    $users = [
        ['owner', $defaultPassword, 'homeOwner'],
        ['adult', $defaultPassword, 'homeAdult'],
        ['kid', $defaultPassword, 'homeKid'],
        ['guest', $defaultPassword, 'guest']
    ];

    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    foreach ($users as $user) {
        $stmt->bind_param("sss", $user[0], $user[1], $user[2]);
        $stmt->execute();
    }
    // need to do this better way 
    $ownerId = $conn->insert_id - 3; // owner was first of 4
    if ($ownerId < 1) $ownerId = 1;

    $conn->query("INSERT INTO homes (name, owner_id) VALUES ('My Smart Home', $ownerId)");
    $homeId = $conn->insert_id;
    
    $conn->query("UPDATE users SET home_id = $homeId");

    echo "Default users and home seeded successfully with password: <b>$defaultPassword</b><br>";
    }
} else {
    http_response_code(500);
    echo "Error checking users table for seeding: " . $conn->error . "<br>";
}

echo "Setup complete!";
$conn->close();
?>