<?php
$host = 'localhost';
$username = 'root';
$password = '';

try {
    // Izveido savienojumu bez datu bāzes
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Izveido datu bāzi
    $pdo->exec("CREATE DATABASE IF NOT EXISTS photo_app");
    $pdo->exec("USE photo_app");
    
    // Izveido tabulu
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    echo "Database and table created successfully!";
    
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>