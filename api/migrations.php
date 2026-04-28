<?php
$conn = new mysqli("localhost", "root", "");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$conn->query("CREATE DATABASE IF NOT EXISTS projectdb");
$conn->select_db("projectdb");

// Create users table
$tableQuery = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL
)";

if ($conn->query($tableQuery)) {
    echo "Table 'users' created or already exists.<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// Seed default users if table is empty
$checkEmpty = $conn->query("SELECT COUNT(*) as count FROM users");
$row = $checkEmpty->fetch_assoc();

if ($row['count'] == 0) {
    $users = [
        ['owner', password_hash('owner123', PASSWORD_DEFAULT), 'homeOwner'],
        ['adult', password_hash('adult123', PASSWORD_DEFAULT), 'homeAdult'],
        ['kid', password_hash('kid123', PASSWORD_DEFAULT), 'homeKid'],
        ['guest', password_hash('guest123', PASSWORD_DEFAULT), 'guest']
    ];

    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    foreach ($users as $user) {
        $stmt->bind_param("sss", $user[0], $user[1], $user[2]);
        $stmt->execute();
    }
    echo "Default users seeded successfully!<br>";
}

echo "Setup complete!";
$conn->close();
?>