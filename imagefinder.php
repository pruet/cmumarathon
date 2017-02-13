<?php
require_once __DIR__ . '/config.php';

$bib = strval($_GET['bib']);
$pass =$_GET['pass'];

// for dev
header('Access-Control-Allow-Origin: *');  

if(isset($pass) && ($pass == $imagefinder_pass) && isset($bib)) {
  $m = new MongoClient();
  $db = $m->cmumarathon;
  if(($doc = $db->runnerimage->find(array('bib' => $bib))) != NULL) {
    if($doc->count() == 0) {
      // no image for this bib
      http_response_code(404);
      return;
    }
    http_response_code(200);
    header('Content-type: text/javascript, charset=utf-8');
    echo json_encode(iterator_to_array($doc), JSON_PRETTY_PRINT); 
    return;
  } else {
    // no bib
    http_response_code(401);
  }
} else {
  // incorrect pass
   http_response_code(405);
}