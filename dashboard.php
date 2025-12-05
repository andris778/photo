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
    <title>Photo</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div class="wrapper">
        <div class="box">
            <div class="col">
                <h2 class="title">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
                <p class="format">You have successfully logged in.</p>
                
                <div class="logout1">
                    <a href="photogalv/photogalv.php" class="logout2">
                       Next
                    </a>
                </div>
                
                <div class="logout1" style="margin-top: 20px;">
                    <a href="logout.php" style="background-color: #494949; color: white; padding: 10px 20px; 
                       text-decoration: none; border-radius: 10px; font-size: 16px; display: inline-block;">
                       Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>