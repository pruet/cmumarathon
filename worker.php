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

function calculatePace($time, $cp)
{
  if($cp == 's') {
    return 'N/A';
  } else if($cp == 'f') {
    $distance = 42.195;
  } else {
    $distance = (int)$cp * 10;
  }
  $str_time = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "00:$1:$2", $time);
  sscanf($str_time, "%d:%d:%d", $hours, $minutes, $seconds);
  $time_seconds = $hours * 3600 + $minutes * 60 + $seconds;
  $pace_seconds = $time_seconds / $distance;
  return ((int)($pace_seconds / 60)) . "'" . ($pace_seconds % 60) . '"';
}

function post_facebook($access_token, $cp, $name, $time)
{
  global $app_id;
  global $app_secret;
  global $default_graph_version;
  $pace = calculatePace($time, $cp);
  // check that we never post it before
  $fb = new Facebook\Facebook([
    'app_id' => $app_id,
    'app_secret' => $app_secret,
    'default_graph_version' => $default_graph_version,
  ]);

  $fb->setDefaultAccessToken($access_token);
  try {
    $response = $fb->get('/me');
    $user = $response->getGraphUser();
  } catch (FacebookResponseException $e) {
    return 500;
  }
  if($user) {
    $pace = calculatePace($time, $cp);
    $image_base = 'https://runnerapi.eng.cmu.ac.th/runnertracker/genpng.php';
    $image_query = 'cp=' . urlencode($cp) . '&name=' . urlencode($name) . '&time=' . urlencode($time) . '&pace=' . urlencode($pace);
    $image = $image_base . '?' . $image_query;
    try {
      $post_data = array(
        'url' => $image
      );
      $apiResponse = $fb->post('/me/photos', $post_data);
      if(!$apiResponse->isError()) {
        return 200;
      }
    } catch (FacebookApiException $e) {
      $user = null;
      return 500;
    }
  } else {
    return 404;
  }
  return 500;
}

$isParent = true;
$myCount = 0;
$pidArray = [];
$childCount = 10;
for($i = 0; $i != $childCount; $i++) {
  $pid = pcntl_fork();
  $pidArray[] = $pid;
  if($pid == -1) {
    die('fork failed');
  } elseif($pid) { //parent
    echo "fork a child with " . $pid;
    echo "\n";
    $isParent = true;
  } else { //children
    $isParent = false;
    $myCount = $i;
    break;
  }
}
$m = new MongoClient();
$db = $m->cmumarathon;
if($isParent) {
  echo "This is parent process\n";
  $count = 0;
  while(true) {
    if(($docs = $db->runnerrequest->find()->limit(100)) != NULL) {
      foreach($docs as $doc) {
        echo "add doc to child #" . $count . "\n";
        //TODO check the response?
        $db->selectCollection("queue" . $count)->insert($doc);
        $db->runnerrequest->remove(array('_id' => $doc['_id']));
        $count++;
        if($count > $childCount) $count = 0;
      }
    }
    sleep(30);
  }
  foreach($pidArray as $pid) {
    pcntl_waitpid($pid, $status);
    echo "Child "  . $pid . " with status " . $status . "\n";
  }
} else { // child
  $myCol = $db->selectCollection("queue" . $myCount);
  echo "This is child #" . $myCount . "\n";
  while(true) {
    if(($doc = $myCol->findOne())!= NULL) {
      // post facebook
      if($db->postlog->count(array('bib' => $doc['bib'], 'cp' => $doc['cp'])) == 0) {
        echo "Child " . $myCount . " post FB bib:" . $doc['bib'] . " cp:" . $doc['cp']. "\n";
        $ret = post_facebook($doc['token'], $doc['cp'], $doc['runner'], $doc['time'] );
        if($ret == 200 || $ret == 404) {
          if($ret == 200) {
            // remove from postlog
            $db->postlog->insert($doc);
          }
          // remove from queue
          $myCol->remove(array('_id' => $doc['_id']));
        }
      } else {
          $myCol->remove(array('_id' => $doc['_id']));
      }
    } else {
      sleep(30);
    }
  }
}