<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../photosak.php");
    exit();
}

require_once '../config.php';

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

$message = '';
$message_type = ''; // 'success' vai 'error'

// Apstrādā augšupielādi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image'])) {
    $description = trim($_POST['description'] ?? '');
    
    // Pārbauda augšupielādes kļūdas
    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        switch ($_FILES['image']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $message = 'File is too large. Maximum size is 2MB.';
                break;
            case UPLOAD_ERR_PARTIAL:
                $message = 'File was only partially uploaded.';
                break;
            case UPLOAD_ERR_NO_FILE:
                $message = 'No file was uploaded.';
                break;
            default:
                $message = 'Upload error occurred.';
        }
        $message_type = 'error';
    } else {
        // Validācija
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        $file_type = $_FILES['image']['type'];
        $file_size = $_FILES['image']['size'];
        $original_name = basename($_FILES['image']['name']);
        $tmp_name = $_FILES['image']['tmp_name'];
        
        // Pārbauda faila tipu
        if (!in_array($file_type, $allowed_types)) {
            $message = 'Only JPG, JPEG, PNG, GIF, and WEBP files are allowed.';
            $message_type = 'error';
        }
        // Pārbauda faila izmēru
        elseif ($file_size > $max_size) {
            $message = 'File size exceeds 2MB limit.';
            $message_type = 'error';
        }
        // Pārbauda vai fails ir reāls attēls
        elseif (!getimagesize($tmp_name)) {
            $message = 'File is not a valid image.';
            $message_type = 'error';
        } else {
            // Izveido unikālu faila nosaukumu
            $file_ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
            $new_filename = uniqid('img_', true) . '_' . $user_id . '.' . $file_ext;
            $upload_dir = 'uploads/';
            
            // Izveido uploads direktoriju, ja tāda nav
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $destination = $upload_dir . $new_filename;
            
            // Pārvieto augšupielādēto failu
            if (move_uploaded_file($tmp_name, $destination)) {
                try {
                    // Saglabā informāciju datu bāzē
                    $stmt = $pdo->prepare("INSERT INTO photos (user_id, filename, original_name, description) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$user_id, $new_filename, $original_name, $description]);
                    
                    $message = 'Image uploaded successfully!';
                    $message_type = 'success';
                    
                    // Notīra POST datus, lai pārlādējot lapu, attēls netiktu augšupielādēts atkārtoti
                    $_POST = array();
                } catch(PDOException $e) {
                    $message = 'Database error: ' . $e->getMessage();
                    $message_type = 'error';
                    // Dzēš augšupielādēto failu, ja datubāzes kaut kāda kļūda
                    if (file_exists($destination)) {
                        unlink($destination);
                    }
                }
            } else {
                $message = 'Failed to move uploaded file.';
                $message_type = 'error';
            }
        }
    }
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
    
    <div class="upload-container">
        <div class="upload-header">
            <h1 style="color: white; font-size: 24px;">Upload New Image</h1>
            <a href="photogalv.php" class="back-button">← Back to Main Page</a>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <form action="upload_image.php" method="POST" enctype="multipart/form-data" class="upload-form">
            <div class="form-group">
                <label for="image" class="form-label">Select Image</label>
                <input type="file" name="image" id="image" accept="image/*" required class="form-input file-input">
            </div>
            
            <div class="form-group">
                <label for="description" class="form-label">Description (Optional)</label>
                <textarea name="description" id="description" class="form-input" placeholder="Enter image description..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
            </div>
            
            <button type="submit" class="upload-btn">Upload Image</button>
        </form>
        
        <div class="upload-info">
            <h3>Upload Guidelines:</h3>
            <ul>
                <li>Maximum file size: 2MB</li>
                <li>Allowed formats: JPG, JPEG, PNG, GIF, WEBP</li>
                <li>Images will be automatically resized if needed</li>
                <li>Make sure images are clear and well-lit</li>
                <li>Respect copyright and privacy</li>
            </ul>
        </div>
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
        
        // File input preview
        document.getElementById('image').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                console.log('File selected:', file.name);
            }
        });
    </script>
</body>
</html>