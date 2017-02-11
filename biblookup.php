<?php
//require_once __DIR__ . '/vendor/autoload.php';
//require_once __DIR__ . '/config.php';

$bib = intval($_GET['bib']);
$pass =$_GET['pass'];

// for dev
//header('Access-Control-Allow-Origin: *');  

if(isset($pass) && ($pass == 'hohohohomerryxmas') && isset($bib)) {
  $m = new MongoClient();
  $db = $m->cmumarathon;
  if(($doc = $db->runnerinfo->findOne(array('bib' => intval($bib)))) != NULL) {
    $doc['_id'] = NULL;
    $doc['id'] = NULL;
    $doc['bod'] = NULL;
    $doc['blood'] = NULL;
    $doc['size'] = NULL;
    $doc['email'] = NULL;
    $doc['sex'] = NULL;
    $doc['groupRunner'] = NULL;
    $doc['bib'] = NULL;
    $doc['age'] = NULL;
    $doc['group'] = NULL;
    $doc['grouprange'] = NULL;
    http_response_code(200);
    header('Content-type: text/javascript, charset=utf-8');
    echo json_encode($doc, JSON_PRETTY_PRINT); 
  } else {
    http_response_code(404);
  }
} else {
   http_response_code(405);
}
