<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../photosak.php");
    exit();
}

require_once '../config.php';

if (isset($_GET['id'])) {
    $photo_id = (int)$_GET['id'];
    
    try {
        //Iegūst attēlu no jebkura lietotāja, nevis tikai pašreizējā lietotāja
        $stmt = $pdo->prepare("SELECT filename, original_name FROM photos WHERE id = ?");
        $stmt->execute([$photo_id]);
        $photo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($photo) {
            // Absolūts ceļš
            $filepath = dirname(__FILE__) . '/uploads/' . basename($photo['filename']);
            
            if (file_exists($filepath) && is_file($filepath)) {
                // Nosaka Content-Type
                $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
                $mime_types = [
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                    'webp' => 'image/webp'
                ];
                
                $mime = $mime_types[$extension] ?? 'application/octet-stream';
                
                // Nosūta attēlu
                header('Content-Type: ' . $mime);
                header('Content-Length: ' . filesize($filepath));
                header('Content-Disposition: inline; filename="' . $photo['original_name'] . '"');
                readfile($filepath);
                exit();
            }
        }
    } catch(PDOException $e) {
        // Ielogo kļūdu
        error_log("View image error: " . $e->getMessage());
    }
}

// Ja kaut kas nogāja greizi
header("HTTP/1.0 404 Not Found");
echo "Image not found";
exit();
?>