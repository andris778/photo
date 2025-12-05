<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../photosak.php");
    exit();
}

require_once '../config.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['photo_id'])) {
    $photo_id = (int)$_POST['photo_id'];
    
    try {
        // Pārbauda vai attēls pieder lietotājam
        $stmt = $pdo->prepare("SELECT filename FROM photos WHERE id = ? AND user_id = ?");
        $stmt->execute([$photo_id, $user_id]);
        $photo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($photo) {
            // ABSOLŪTS CEĻŠ
            $filename = basename($photo['filename']);
            $file_path = dirname(__FILE__) . '/uploads/' . $filename;
            
            // Pārbauda vai fails eksistē un ir attēls
            if (file_exists($file_path) && is_file($file_path)) {
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
                
                if (in_array($extension, $allowed_extensions)) {
                    unlink($file_path);
                }
            }
            
            // Dzēš ierakstu no datu bāzes
            $stmt = $pdo->prepare("DELETE FROM photos WHERE id = ? AND user_id = ?");
            $stmt->execute([$photo_id, $user_id]);
            
            $_SESSION['success'] = "Image deleted successfully.";
        } else {
            $_SESSION['error'] = "Image not found or you don't have permission to delete it.";
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error deleting image: " . $e->getMessage();
    }
}

// Atgriežas uz uploads lapu
header("Location: uploads.php");
exit();
?>