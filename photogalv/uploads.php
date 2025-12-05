<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../photosak.php");
    exit();
}

require_once '../config.php';

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Iegūst lietotāja attēlus
try {
    $stmt = $pdo->prepare("
        SELECT id, filename, original_name, description, uploaded_at 
        FROM photos 
        WHERE user_id = ? 
        ORDER BY uploaded_at DESC
    ");
    $stmt->execute([$user_id]);
    $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $photos = [];
    $error = "Failed to load images: " . $e->getMessage();
    error_log("Uploads error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Photo</title>
    <link rel="stylesheet" href="photogalv.css">
</head>
<body>
    <div class="wrapper">
        <div class="topnav">
            <img class="imgc" src="camera.png" width="50px" height="50px" alt="Camera">
            <h1 class="title">Photo</h1>
            
            <div class="dropdown">
                <button class="menu-btn" id="menuButton" type="button" onclick="toggleMenu()">
                    <img class="imeg" src="profile.jpg" alt="Profile">
                </button>
                <div class="dropdown-content" id="dropdownMenu">
                    <div class="menu-section">Account</div>
                    <a href="profile/profile.php">Profile</a>
                    
                    <div class="menu-section">Image</div>
                    <a href="uploads.php">Uploads</a>
                    <a href="upload_image.php">Upload image</a>
                    
                    <a href="../logout.php" class="logout-link">Logout</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="uploads-container">
        <div class="uploads-header">
            <h1 style="color: white; font-size: 24px;">My Uploaded Images</h1>
            <div>
                <a href="photogalv.php" class="back-button">← Back</a>
                <a href="upload_image.php" class="upload-new">+ Upload New</a>
            </div>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="message error" style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($photos)): ?>
            <div class="no-photos">
                <h3 style="color: white; margin-bottom: 15px;">No images uploaded yet</h3>
                <p style="color: #888; margin-bottom: 20px;">Start by uploading your first image!</p>
                <a href="upload_image.php" class="upload-btn-large">Upload Your First Image</a>
            </div>
        <?php else: ?>

            <div class="photos-grids">
                <?php foreach ($photos as $photo): ?>
                    <div class="photos-card">

                    <?php
// Vienkāršs attēla ceļš
$image_filename = htmlspecialchars($photo['filename']);
$image_path_local = 'uploads/' . $image_filename;
$image_path_url = 'uploads/' . $image_filename;

// Pārbauda, vai fails eksistē
if (!file_exists($image_path_local)) {
    $image_path_url = 'placeholder.jpg';
}
?>

<img src="<?php echo $image_path_url; ?>" 
     alt="<?php echo htmlspecialchars($photo['original_name']); ?>" 
     class="photo-image">

                        
                        <div class="photo-info">
                            <div class="photo-name">
                                <?php echo htmlspecialchars(pathinfo($photo['original_name'], PATHINFO_FILENAME)); ?>
                            </div>
                            
                            <?php if (!empty($photo['description'])): ?>
                                <div class="photo-description">
                                    <?php echo htmlspecialchars($photo['description']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="photo-date">
                                Uploaded: <?php echo date('M j, Y', strtotime($photo['uploaded_at'])); ?>
                            </div>
                            
                            <div class="photo-actions">
                                <a href="view_image.php?id=<?php echo $photo['id']; ?>" target="_blank" class="action-btn">View</a>
                                <form action="delete_image.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="photo_id" value="<?php echo $photo['id']; ?>">
                                    <button type="submit" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this image?');">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="upload-message">
                <p style="color: #888; margin-top: 30px;">Total images: <?php echo count($photos); ?></p>
                <a href="upload_image.php" class="upload-btn-large">Upload More Images</a>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Menu scripts
        let menuOpen = false;
        
        function toggleMenu() {
            const menu = document.getElementById('dropdownMenu');
            menuOpen = !menuOpen;
            
            if (menuOpen) {
                menu.classList.add('show');
            } else {
                menu.classList.remove('show');
            }
        }
        
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('dropdownMenu');
            const menuBtn = document.getElementById('menuButton');
            
            if (menuOpen && !menu.contains(event.target) && !menuBtn.contains(event.target)) {
                menu.classList.remove('show');
                menuOpen = false;
            }
        });
    </script>
</body>
</html>