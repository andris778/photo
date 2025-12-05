<?php
// Izveido vienkāršu placeholder attēlu
$width = 250;
$height = 200;

$image = imagecreatetruecolor($width, $height);

// Krāsas
$background = imagecolorallocate($image, 43, 43, 43); 
$text_color = imagecolorallocate($image, 255, 255, 255); 

// Aizpilda fonu
imagefilledrectangle($image, 0, 0, $width, $height, $background);

// Pievieno tekstu
$text = "No Image";
$font = 5; // Iebūvētais fonts
$text_width = imagefontwidth($font) * strlen($text);
$text_height = imagefontheight($font);

$x = ($width - $text_width) / 2;
$y = ($height - $text_height) / 2;

imagestring($image, $font, $x, $y, $text, $text_color);

// Saglabā attēlu
header('Content-Type: image/jpeg');
imagejpeg($image, 'placeholder.jpg', 80);
imagedestroy($image);

echo "Placeholder image created successfully!";
?>