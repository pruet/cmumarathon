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

if(!session_id()) {
  session_start();
}

// Check request type
$type = clean($_POST["type"]);
if($type == 'json') {
  $js = json_decode($_POST['payload']);
  $bib = clean($js->bib);
  $cp = clean($js->cp);
  $time = clean($js->time);
  $pass = clean($js->pass);
} else {
  $bib = clean($_POST["bib"]);
  $cp = clean($_POST["cp"]);
  $time = clean($_POST["time"]);
  $pass = clean($_POST["tk"]);
}

// Check parameters
if(isset($pass) && ($pass == '7uZZs8RwpNnWjP5jHzsDTsA1CQGR') && isset($bib) && isset($cp) && isset($time)) {
  $m = new MongoClient();
  $db = $m->cmumarathon;
  $collrt = $db->runnertracker;

// check if user (bib) want to post at this location (cp)
  $query = array('bib' => $bib, $cp => 'on');
  if(($doc = $collrt->findOne($query)) != NULL) {
    // check that we never post it before
    if($db->postlog->count(array('bib' => $bib, $cp=> 'on')) == 0) {
      $access_token = $doc["fbsession"];
      $name = $doc["runner"];
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
        print_r($e);
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
            echo "Progress posted.<br />";
            // save to log
            $db->postlog->insert(array('bib' => $bib, $cp=>'on'));
          }
        } catch (FacebookApiException $e) {
          $user = null;
          print_r($e);
        }
      } else {
        echo "no user";
      }
    }
  }
}
?>
<html>
<body>
<table>
<form method="post" action="/runnertracker/post-update.php" >
  <tr><td>Passpharse:</td><td><input type="text" name="tk" /></td></tr>
  <tr><td>BIB:</td><td><input type="text" name="bib" /></td></tr>
  <tr><td>CP:</td><td>
  <select name="cp">
  <option value="s">Start</option>
  <option value="1">10k</option>
  <option value="2">20k</option>
  <option value="3">30k</option>
  <option value="f">Finished</option>
  </select></td></tr>
  <tr><td>time:</td><td><input type="text" name="time" /> (time format: HH:MM:SS)</td></tr>
  <tr><td><input type="submit" /></td><td><td></tr>
</form>
</table>
<body>
<html>
