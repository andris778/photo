<?php
$host = 'localhost';
$dbname = 'photo_app';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // Ja datu bāze neeksistē, mēģina to izveidot
    if ($e->getCode() == 1049) {
        try {
            $pdo = new PDO("mysql:host=$host", $username, $password);
            $pdo->exec("CREATE DATABASE $dbname");
            $pdo->exec("USE $dbname");
            $pdo->exec("CREATE TABLE users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            
            // Pārslēdzas atpakaļ uz izveidoto datu bāzi
            $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            
        } catch(PDOException $e2) {
            die("Could not create database: " . $e2->getMessage());
        }
    } else {
        die("Connection failed: " . $e->getMessage());
    }
}
?>