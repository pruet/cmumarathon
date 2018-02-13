<?php
require_once __DIR__ . '/config.php';

$pass =$_GET['pass'];

// for dev
header('Access-Control-Allow-Origin: *');  

if(isset($pass) && ($pass == $imagefinder_pass)) {
  $m = new MongoClient();
  $db = $m->selectDB($racedb);
  if(($doc = $db->photographer->find()) != NULL) {
    if($doc->count() == 0) {
      // no image for this bibib
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
}
