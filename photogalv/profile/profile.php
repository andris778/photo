<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../photosak.php");
    exit();
}

require_once '../../config.php';

// Iegūst lietotāja informāciju
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Iegūst papildus informāciju no datu bāzes
try {
    $stmt = $pdo->prepare("SELECT created_at FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $user_data = ['created_at' => 'N/A'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - <?php echo htmlspecialchars($username); ?></title>
    <link rel="stylesheet" href="profile.css">
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <h1>User Profile</h1>
            <a href="../photogalv.php" class="back-button">← Back to Main Page</a>
        </div>
        
        <div class="profile-info">
            <div class="profile-picture">
                <img src="../profile.jpg" alt="Profile Picture">
                <h3><?php echo htmlspecialchars($username); ?></h3>
            </div>
            
            <div class="profile-details">
                <h2>Account Information</h2>
                
                <div class="detail-item">
                    <div class="detail-label">Username</div>
                    <div class="detail-value"><?php echo htmlspecialchars($username); ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">User ID</div>
                    <div class="detail-value"><?php echo htmlspecialchars($user_id); ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Account Created</div>
                    <div class="detail-value"><?php echo date('F j, Y', strtotime($user_data['created_at'])); ?></div>
                </div>
                
                <div class="action-buttons">
                    <a href="../../logout.php" class="action-button logout-btn">Logout</a> 
                </div>
            </div>
        </div>
    </div>
</body>
</html>