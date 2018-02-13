<?php
require_once __DIR__ . '/config.php';

$m = new MongoClient();
$db = $m->selectDB($racedb);

$row = 1;
$colNum = 4;
if(($handle = fopen('data.csv', 'r')) !== FALSE) {
  while(($data = fgetcsv($handle, 1000, ',')) != FALSE) {
    $bib = intval($data[0]);
    if($bib < 10000) { // full/half/mini
      if($bib < 10) {
        $bib = '000' . strval($bib);
      } else if($bib < 100) {
        $bib = '00' . strval($bib);
      } else if($bib < 1000) {
        $bib = '0' . strval($bib);
      }
    } 
    $bib = strval($bib);
    $href = strval($data[1]);
    $url = strval($data[2]);
    $photographer = strval($data[3]);
    $db->runnerimage->insert(array(
      'bib' => $bib,
      'href' => $href,
      'url' => $url,
      'photographer' => $photographer
    ));
    $row++;
  }
  echo 'Inserted ' . strval($row);
  echo '\n';
}
