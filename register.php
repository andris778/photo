<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validācija
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "All fields are required";
        header("Location: photosak.php");
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match";
        header("Location: photosak.php");
        exit();
    }

    if (strlen($password) < 6) {
        $_SESSION['error'] = "Password must be at least 6 characters long";
        header("Location: photosak.php");
        exit();
    }

    try {
        // Pārbauda, vai lietotājvārds jau eksistē
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = "Username already exists";
            header("Location: photosak.php");
            exit();
        }

        // Hasho paroli un saglabā lietotāju
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $hashed_password]);

        $_SESSION['success'] = "Registration successful! You can now login.";
        header("Location: photosak.php");
        exit();

    } catch(PDOException $e) {
        $_SESSION['error'] = "Registration failed: " . $e->getMessage();
        header("Location: photosak.php");
        exit();
    }
}
?>