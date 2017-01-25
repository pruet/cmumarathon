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

function postFacebook($access_token, $cp, $name, $time, $pace)
{
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
    if(($docs = $db->request->find()->limit(100)) != NULL) {
      $col = $db->selectCollection("queue" . $count);
      foreach($docs as $doc) {
        $col->insert($doc);
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
  echo "This is child #" . $myCount . "\n";
  while(true) {
    if(($doc = $db->selectCollection("queue" + $myCount)->findOne())!= NULL) {
      print_r($doc);
    }
  }
}