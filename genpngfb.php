<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

$bib = strval($_GET["bib"]);
$base_image = 'images/badge-fb.png';
$font = './niramit.ttf';

if(isset($bib)) {
  $json = file_get_contents('https://marathon.eng.cmu.ac.th/AllResult/getRunnerInfo/?id=' . $bib);
  $runnerinfo = json_decode($json);
  print_r($runnerinfo);
  return;
  $im = imagecreatefrompng($base_image);
  $white = imagecolorallocate($im, 0xFF, 0xFF, 0xFF);
  imagefttext($im, 60, 0, 290, 530, $white, $font, $name);
  imagefttext($im, 60, 0, 190, 640, $white, $font, $time);
  imagefttext($im, 60, 0, 550, 640, $white, $font, $pace);
  header('Content-Type: image/png');
  imagepng($im);
  imagedestroy($im);
}

?>
