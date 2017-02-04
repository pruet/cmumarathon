<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

function clean($in)
{
  return $in;
  $t = trim($in);
  $s = strip_tags($t);
  $h = htmlspecialchars($s);
  return $h;
}

$bib = $_GET['bib'];
$pass =$_GET['pass'];

if(isset($pass) && ($pass == '7uZZs8RwpNnWjP5jHzsDTsA1CQGR') && isset($bib)) {
  $m = new MongoClient();
  $db = $m->cmumarathon;
  $collinfo = $db->runnerinfo;
  if(($doc = $db->runnerinfo->find(array('bib' => $bib))) != NULL) {
    http_response_code(200);
    header('Content-type: text/javascript');
    echo json_encode($doc, JSON_PRETTY_PRINT); 
  }
} else {
   http_response_code(404);
}