<?php
$bib = strval($_GET["bib"]);
$base_image = 'images/badge-fb.png';
$font = './niramit.ttf';

if(isset($bib)) {
  $json = file_get_contents('https://marathon.eng.cmu.ac.th/AllResult/getRunnerInfo/?id=' . $bib);
  $ri = json_decode($json, true);
  $gender = ($ri['Sex']==1)?"Male":"Female";
  $country = $ri['Nation'];
  $type = $ri['Type'];
  $rank = $ri['PlaceAll'];
  $genrank = $ri['place_by_gender'];
  $catrank = $ri['PlaceCat'];
  $name = $ri['Name'];
  $time = $ri['FinishTime'];
  $im = imagecreatefrompng($base_image);
  $white = imagecolorallocate($im, 0xFF, 0xFF, 0xFF);
  $black = imagecolorallocate($im, 0x00, 0x00, 0x00);
  imagefttext($im, 60, 0, 290, 530, $white, $font, $name);
  imagefttext($im, 60, 0, 190, 640, $white, $font, $time);
  imagefttext($im, 55, 0, 260, 760, $black, $font, $gender);
  imagefttext($im, 55, 0, 680, 760, $black, $font, $country);
  imagefttext($im, 55, 0, 190, 840, $black, $font, $type);
  imagefttext($im, 55, 0, 615, 840, $black, $font, $rank);
  imagefttext($im, 55, 0, 330, 925, $black, $font, $genrank);
  imagefttext($im, 55, 0, 315, 1005, $black, $font, $catrank);
  header('Content-Type: image/png');
  imagepng($im);
  imagedestroy($im);
}

?>
