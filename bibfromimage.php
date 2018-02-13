<?php
require_once __DIR__ . '/config.php';

$url = urldecode(strval($_GET['url']));
$pass =$_GET['pass'];

// for dev
header('Access-Control-Allow-Origin: *');  

if(isset($pass) && ($pass == $imagefinder_pass) && isset($url)) {
  $m = new MongoClient();
  $db = $m->selectDB($racedb);
  if(($docs = $db->runnerimage->find(array('url' => $url))) != NULL) {
    if($docs->count() == 0) {
      // no image for this bibib
      http_response_code(404);
      return;
    }
    http_response_code(200);
    header('Content-type: text/javascript, charset=utf-8');
    $out = array();
    foreach($docs as $doc) {
      $out[] = $doc['bib'];
    }
    echo json_encode($out, JSON_PRETTY_PRINT); 
    return;
  } else {
    // no bib
    http_response_code(401);
  }
}
