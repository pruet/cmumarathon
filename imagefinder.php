<?php
require_once __DIR__ . '/config.php';

$bib = strval($_GET['bib']);
$pass =$_GET['pass'];

// for dev
header('Access-Control-Allow-Origin: *');  

if(isset($pass) && ($pass == $imagefinder_pass) && isset($bib)) {
  $m = new MongoClient();
  $db = $m->selectDB($racedb);
  if(($doc = $db->runnerimage->find(array('bib' => $bib))) != NULL) {
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
} else {
  $pass = $_POST['pass'];
  if(isset($pass) && $pass == $imagefinder_pass) {
    $m = new MongoClient();
    $db = $m->cmumarathon;
    $action = $_POST['action'];
    $url = $_POST['url'];
    $bib = $_POST['bib'];
    $oldbib = $_POST['oldbib'];
    if($action == "delete") {
      //$db->runnerimage->remove(array('url' => $url, 'bib' => $bib));
      $db->runnerimagereport->insert(array('url' => $url, 'bib' => $bib, 'action' => 'delete', 'status' => 'new'));
    } else { //edit
      $bibs = array_map('trim', explode(',', $bib));
      //$doc = $db->runnerimage->findOne(array('url' => $url, 'bib' => $oldbib));
      //$oldid = $doc['_id'];
      $contain_old_bib = false;
      foreach($bibs as $bib) {
        //unset($doc['_id']);
        $bib = trim($bib);
        //$doc['bib'] = $bib;
        //$db->runnerimage->insert($doc);
        if($bib != $oldbib) {
          $db->runnerimagereport->insert(array('url' => $url, 'bib' => $bib, 'action' => 'add', 'status' => 'new'));
        } else {
          $contain_old_bib = true;
        }
      }
      //$db->runnerimage->remove(array('_id' => $oldid));
      if(!$contain_old_bib) {
        $db->runnerimagereport->insert(array('url' => $url, 'bib' => $oldbib, 'action' => 'delete', 'status' => 'new'));
      }
    }
  } else {
  // incorrect pass
   http_response_code(405);
  }
}
