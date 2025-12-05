<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../photosak.php");
    exit();
}

require_once '../config.php';

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Iegūst VISUS attēlus no datubāzes ar autora informāciju
try {
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.filename,
            p.original_name,
            p.description,
            p.uploaded_at,
            u.username as author_name
        FROM photos p
        JOIN users u ON p.user_id = u.id
        ORDER BY p.uploaded_at DESC
    ");
    $stmt->execute();
    $all_photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $all_photos = [];
    $error = "Failed to load images: " . $e->getMessage();
    error_log("Gallery error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Photo Gallery</title>
    <link rel="stylesheet" href="photogalv.css">
</head>
<body>
    <div class="wrapper">
        <div class="topnav">
            <img class="imgc" src="camera.png" width="50px" height="50px" alt="Camera">
            <h1 class="title">Photo</h1>

            <!-- Dropdown menu -->
            <div class="dropdown">
                <button class="menu-btn" id="menuButton" type="button" onclick="toggleMenu()">
                    <img class="imeg" src="profile.jpg" alt="Profile">
                </button>
                <div class="dropdown-content" id="dropdownMenu">
                    <!-- Account sadaļa -->
                    <div class="menu-section">Account</div>
                    <a href="profile/profile.php">Profile</a>

                    <!-- Image sadaļa -->
                    <div class="menu-section">Image</div>
                    <a href="uploads.php">My Uploads</a>
                    <a href="upload_image.php">Upload image</a>
    
                    <!-- Logout -->
                    <a href="../logout.php" class="logout-link">Logout</a>
                </div>
            </div>
        </div>
    </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="message success" style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="message error" style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="message error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($all_photos)): ?>
            <div class="no-photos">
                <h3>No photos uploaded yet</h3>
                <p>Be the first to upload an image!</p>
                <a href="upload_image.php" class="upload-btn-large">Upload Your First Image</a>
            </div>
        <?php else: ?>
            <div class="photos-grid">
                <?php foreach ($all_photos as $photo): ?>
                    <div class="photo-card">
                        <?php
                        // Attēla ceļš
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
                             class="photo-image"
                             onclick="openImage(<?php echo $photo['id']; ?>)"
                             style="cursor: pointer;">
                        
                        <div class="photo-info">
                            <div class="photo-name">
                                <?php echo htmlspecialchars(pathinfo($photo['original_name'], PATHINFO_FILENAME)); ?>
                            </div>
                            
                            <div class="photo-author">
                                By: <?php echo htmlspecialchars($photo['author_name']); ?>
                            </div>
                            
                            <?php if (!empty($photo['description'])): ?>
                                <div class="photo-description">
                                    <?php echo htmlspecialchars($photo['description']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="photo-date">
                                Uploaded: <?php echo date('M j, Y', strtotime($photo['uploaded_at'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="upload-message">
                <p style="color: #888; margin-top: 30px;">Total photos in gallery: <?php echo count($all_photos); ?></p>
                <a href="upload_image.php" class="upload-btn-large">Upload Your Photo</a>
            </div>
        <?php endif; ?>
    

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
        
        // Aizver menu, ja klikšķina uz jebkura linka (izņemot logout)
        const menu = document.getElementById('dropdownMenu');
        menu.addEventListener('click', function(event) {
            if (event.target.tagName === 'A') {
                if (!event.target.classList.contains('logout-link')) {
                    menu.classList.remove('show');
                    menuOpen = false;
                }
            }
        });
        
        // Atver attēlu jaunā logā (izmantojot view_image.php)
        function openImage(photoId) {
            window.open('view_image.php?id=' + photoId, '_blank');
        }
        
        // Responsive image loading
        const images = document.querySelectorAll('.photo-image');
        images.forEach(img => {
            img.setAttribute('loading', 'lazy');
        });
    </script>
</body>
</html>