<?php
require_once __DIR__ . '/config.php';

$url = urldecode(strval($_GET['url']));
$pass =$_GET['pass'];

// for dev
header('Access-Control-Allow-Origin: *');  

if(isset($pass) && ($pass == $imagefinder_pass) && isset($url)) {
  $m = new MongoClient();
  $db = $m->selectDB($racedb);
  $url = urldecode($url);
  if(($doc = $db->runnerimage->findOne(array('url' => $url))) != NULL) {
    http_response_code(200);
    header('Content-type: text/javascript, charset=utf-8');
    $out['href'] = $doc['href'];
    echo json_encode($out, JSON_PRETTY_PRINT); 
    return;
  } else {
    // no bib
    http_response_code(401);
  }
}
