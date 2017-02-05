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

function calculate_pace($time, $cp, $type)
{
  global $distance;
  $dist = $distance[$type][$cp];
  // in case of no hour
  $str_time = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "00:$1:$2", $time);
  // split into h/m/s
  sscanf($str_time, "%d:%d:%d", $hours, $minutes, $seconds);
  $time_seconds = $hours * 3600 + $minutes * 60 + $seconds;
  $pace_seconds = $time_seconds / $dist;
  // pace in min'sec" per kilometer format
  return ((int)($pace_seconds / 60)) . "'" . ($pace_seconds % 60) . '"';
}

function post_facebook($access_token, $cp, $name, $time, $type, &$api_response) 
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
    $pace = calculate_pace($time, $cp, $type);
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

// syslog
if(!openlog($syslogid, LOG_CONS | LOG_PID | LOG_PERROR, LOG_USER)) {
  echo "Can't open syslog, send message to console";
}

if($is_parent) {
  syslog(LOG_INFO, "Start parent process");
  $count = 0;
  while(true) {
    // get 100 most recent request
    if(($docs = $db->runnerrequest->find()->limit(100)) != NULL) {
      foreach($docs as $doc) {
        syslog(LOG_INFO, "add doc to child #" . $count);
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
    syslog(LOG_INFO, "Child "  . $pid . " with status " . $status);
  }
} else { // child
  $myCol = $db->selectCollection("queue" . $my_count);
  syslog(LOG_INFO, "Start child #" . $my_count);
  while(true) {
    // check my queue
    if(($doc = $myCol->findOne())!= NULL) {
      $bib = $doc['bib'];
      $cp = $doc['cp'];
      $runner = $doc['runner'];
      $time = $doc['time'];
      $token = $doc['token'];
      // got a request, check if it's dubplicated
      if($db->postlog->count(array('bib' => $bib, 'cp' => $cp)) == 0) {
        syslog(LOG_INFO, "Child " . $my_count . " post FB bib:" . $bib . " cp:" . $cp);
        // post facebook
        if($bib <= $full_max) { //1 <= full <= 1000
          $type = 'f';
        } else if($bib = $half_max) { // 1001 <= half <= 3000
          $type = 'h';
        } else { // 3001 <= mini <== 9999
          $type = 'm';
        }
        $ret = post_facebook($token, $cp, $runner, $time , $type, $api_response);
        // save response for future use
        $db->fbresponse->insert(array(
            'bib'=>$bib,
            'token'=>$token,
            'cp'=>$cp,
            'time'=>$time,
            'response'=>json_decode($api_response->getBody())
        ));
        if($ret == 200 || $ret == 404) {
          if($ret == 200) {
            // post ok, save to  postlog
            $db->postlog->insert($doc);
            syslog(LOG_INFO, "Child " . $my_count . " post FB bib:" . $bib . " cp:" . $cp . " successfully");
          } else {
            syslog(LOG_INFO, "Child " . $my_count . " post FB bib:" . $bib . " cp:" . $cp . " got 404");
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