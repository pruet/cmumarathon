<?php

error_log("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

$name = $_GET["name"];
$time = $_GET["time"];
$pace = $_GET["pace"];
$cp = $_GET["cp"];
$base_image = 'images/badge-c' . $cp . '.png';
//$time = '12:34:56';
//$pace = "8'7\"/km";
$font = './niramit.ttf';

$im = imagecreatefrompng($base_image);
$white = imagecolorallocate($im, 0xFF, 0xFF, 0xFF);
imagefttext($im, 24, 0, 290, 480, $white, $font, $name);
imagefttext($im, 22, 0, 210, 590, $white, $font, $time);
imagefttext($im, 22, 0, 500, 590, $white, $font, $pace);
header('Content-Type: image/png');
imagepng($im);
imagedestroy($im);
?>
