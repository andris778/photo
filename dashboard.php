<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: photosak.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Photo App</title>
    <link rel="stylesheet" href="photosak.css">
</head>
<body>
    <div class="wrapper">
        <div class="box">
            <div class="col">
                <h2 class="title">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
                <p class="userandpasw" style="text-align: center; margin-bottom: 20px;">You have successfully logged in.</p>
                
                <div style="text-align: center;">
                    <a href="logout.php" style="background-color: #494949; color: white; padding: 15px 30px; 
                       text-decoration: none; border-radius: 10px; font-size: 20px; display: inline-block;">
                       Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>