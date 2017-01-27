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

function calculate_pace($time, $cp)
{
  // special case
  if($cp == 's') {
    return 'N/A';
  } else if($cp == 'f') {
    $distance = 42.195;
  } else {
    $distance = (int)$cp * 10;
  }
  // in case of no hour
  $str_time = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "00:$1:$2", $time);
  // split into h/m/s
  sscanf($str_time, "%d:%d:%d", $hours, $minutes, $seconds);
  $time_seconds = $hours * 3600 + $minutes * 60 + $seconds;
  $pace_seconds = $time_seconds / $distance;
  // pace in min'sec" per kilometer format
  return ((int)($pace_seconds / 60)) . "'" . ($pace_seconds % 60) . '"';
}

function post_facebook($access_token, $cp, $name, $time)
{
  global $app_id;
  global $app_secret;
  global $default_graph_version;
  global $image_base;
  $fb = new Facebook\Facebook([
    'app_id' => $app_id,
    'app_secret' => $app_secret,
    'default_graph_version' => $default_graph_version,
  ]);

  // Get runner identity from token
  $fb->setDefaultAccessToken($access_token);
  try {
    $response = $fb->get('/me');
    $user = $response->getGraphUser();
  } catch (FacebookResponseException $e) {
    return 500;
  }
  if($user) {
    $pace = calculate_pace($time, $cp);
    // construct post body
    $image_query = 'cp=' . urlencode($cp) . '&name=' . urlencode($name) . '&time=' . urlencode($time) . '&pace=' . urlencode($pace);
    $image = $image_base . '?' . $image_query;
    $post_data = array(
      'url' => $image
    );
    // fire a post
    try {
      $api_response = $fb->post('/me/photos', $post_data);
      if(!$api_response->isError()) {
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

// main
$is_parent = true;
$my_count = 0;
$pid_array = [];
// fork children
for($i = 0; $i != $child_count; $i++) {
  $pid = pcntl_fork();
  if($pid == -1) {
    die('fork failed');
  } elseif($pid) { //I'm the parent
    $pid_array[] = $pid;
    echo "fork a child with " . $pid . " \n";
    $is_parent = true;
  } else { //I'm a child
    $is_parent = false;
    $my_count = $i;
    break;
  }
}

// connect db
$m = new MongoClient();
$db = $m->cmumarathon;

if($is_parent) {
  echo "This is parent process\n";
  $count = 0;
  while(true) {
    // get 100 most recent request
    if(($docs = $db->runnerrequest->find()->limit(100)) != NULL) {
      foreach($docs as $doc) {
        echo "add doc to child #" . $count . "\n";
        // add to child's queue
        $db->selectCollection("queue" . $count)->insert($doc);
        // remove from request queue
        $db->runnerrequest->remove(array('_id' => $doc['_id']));
        // round robbin here
        $count++;
        if($count > $child_count) $count = 0;
      }
    }
    // do some sleep to avoid spin-lock
    sleep($parent_delay);
  }
  // just in case....
  foreach($pid_array as $pid) {
    pcntl_waitpid($pid, $status);
    echo "Child "  . $pid . " with status " . $status . "\n";
  }
} else { // child
  $myCol = $db->selectCollection("queue" . $my_count);
  echo "This is child #" . $my_count . "\n";
  while(true) {
    // check my queue
    if(($doc = $myCol->findOne())!= NULL) {
      // got a request, check if it's dubplicated
      if($db->postlog->count(array('bib' => $doc['bib'], 'cp' => $doc['cp'])) == 0) {
        echo "Child " . $my_count . " post FB bib:" . $doc['bib'] . " cp:" . $doc['cp']. "\n";
        // post facebook
        $ret = post_facebook($doc['token'], $doc['cp'], $doc['runner'], $doc['time'] );
        if($ret == 200 || $ret == 404) {
          if($ret == 200) {
            // post ok, save to  postlog
            $db->postlog->insert($doc);
          }
          // remove from queue
          $myCol->remove(array('_id' => $doc['_id']));
        }
      } else {
          $myCol->remove(array('_id' => $doc['_id']));
      }
    } else {
      // take some nap to avoid spin lock
      sleep($child_delay);
    } // if has doc
  } // while
} // if child