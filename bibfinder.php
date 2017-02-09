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

$txt = $_GET['txt'];
$pass =$_GET['pass'];

// for dev
header('Access-Control-Allow-Origin: *');  

if(isset($pass) && ($pass == 'hohohohomerryxmas') && isset($txt)) {
  $m = new MongoClient();
  $db = $m->cmumarathon;
  $regex = new MongoRegex("/" . $txt . "/i");
  $searchQuery = array(
    '$or' => array (
      array(
        'fname' => new MongoRegex("/$txt/i"),
      ),
      array(
        'lname' => new MongoRegex("/$txt/i"),
      ),
      array(
        'tfname' => new MongoRegex("/$txt/i"),
      ),
      array(
        'flname' => new MongoRegex("/$txt/i"),
      ),
      array(
        'id' => new MongoRegex("/$txt/i"),
      )
    ) 
  );
  if(($doc = $db->runnerinfo->find($searchQuery)) != NULL) {
    if($doc->count() == 0) {
      http_response_code(404);
      return;
    }
    http_response_code(200);
    header('Content-type: text/javascript, charset=utf-8');
    echo json_encode(iterator_to_array($doc), JSON_PRETTY_PRINT); 
    return;
  } else {
    http_response_code(404);
  }
} else {
   http_response_code(405);
}