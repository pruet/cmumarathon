<?php
require_once __DIR__ . '/config.php';

function calculate_pace($time, $cp, $type)
{
  global $distance;
  if($cp == 's') {
    return "N/A";
  }
  $dist = $distance[$type][$cp];
  // in case of no hour
  $str_time = preg_replace('/^([\d]{1,2})\:([\d]{2})$/', '00:$1:$2', $time);
  // split into h/m/s
  sscanf($str_time, '%d:%d:%d', $hours, $minutes, $seconds);
  $time_seconds = $hours * 3600 + $minutes * 60 + $seconds;
  $pace_seconds = $time_seconds / $dist;
  // pace in min'sec" per kilometer format
  return ((int)($pace_seconds / 60)) . '\'' . ($pace_seconds % 60) . '"';
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

  $pace = calculate_pace($time, $cp, $type);
  // construct post body
  $image_query = 'cp=' . urlencode($cp) . '&name=' . urlencode($name) . '&time=' . urlencode($time) . '&pace=' . urlencode($pace);
  $image = $image_base . '?' . $image_query;
  $post_data = array(
    'url' => $image
  );
  // fire a post
  try {
    $api_response = $fb->post('/me/photos', $post_data, $access_token);
    if(!$api_response->isError()) {
      return 200;
    }
  } catch(Facebook\Exceptions\FacebookResponseException $e) {
    echo 'Facebook response returned an error: ' . $e->getMessage();
  } catch(Facebook\Exceptions\FacebookSDKException $e) {
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
  } catch (Exception $e) {
    echo 'General error: ' . $e->getMessage();
  }
  return 500;
}

function mydatediff($date_1 , $date_2 , $differenceFormat = '%h:%i:%s')
{
  $datetime1 = date_create($date_1);
  $datetime2 = date_create($date_2);

  $interval = date_diff($datetime1, $datetime2);

  return $interval->format($differenceFormat);

}


//main
$is_parent = true;
$my_count = 0;
$pid_array = [];
// fork children
for($i = 0; $i != $worker_child_count; $i++) {
  $pid = pcntl_fork();
  if($pid == -1) {
    die('fork failed');
  } elseif($pid) { //I'm you father
    $pid_array[] = $pid;
    echo 'fork a child with ' . $pid . '\n';
    $is_parent = true;
  } else { //Nooooooooooooooooooooooooooooo
    $is_parent = false;
    $my_count = $i;
    break;
  }
}

// connect db
$m = new MongoClient();
$db = $m->cmumarathon;

if(!openlog($syslogid, LOG_CONS | LOG_PID | LOG_PERROR, LOG_LOCAL7)) {
  echo 'Can\'t open syslog, send message to console';
}

if($is_parent) {
  syslog(LOG_INFO, 'Start parent process');
  $count = 0;
  while(true) {
    $docs = $db->runnerrequest->find()->limit(1000);
    $num = $docs->count();
    if($num > 0) {
      foreach($docs as $doc) {
        // add to child's queue
        syslog(LOG_INFO, "add doc to child #" . $count);
        // check the checklog for duplication
        if($db->checklog->count(array('bib' => $doc['bib'], 'cp' => $doc['cp'])) == 0) {
          $db->selectCollection('queue' . $count)->insert($doc);
          // add to checklog to prevent duplication
          $db->checklog->insert($doc);
        }
        // remove from request queue
        $db->runnerrequest->remove(array('_id' => $doc['_id']));
        // round robbin here
        $count++;
        if($count > $worker_child_count) $count = 0;
      }
    }
    // do some sleep to avoid spin-lock
    syslog(LOG_INFO, 'running at ' . ($num/$worker_parent_delay) . ' rps' );
    sleep($worker_parent_delay);
  }
  // just in case....
  foreach($pid_array as $pid) {
    pcntl_waitpid($pid, $status);
    syslog(LOG_INFO, 'Child '  . $pid . ' exit with status ' . $status);
  }
} else { // child
  $myCol = $db->selectCollection('queue' . $my_count);
  syslog(LOG_INFO, 'Start child #' . $my_count);
  while(true) {
    // check my queue
    if(($doc = $myCol->findOne())!= NULL) {
      $bib = $doc['bib'];
      $cp = $doc['cp'];
      $runner = $doc['runner'];
      $time = $doc['time'];
      $token = $doc['token'];
      // got a request, check if it's dubplicated, again
      if($db->postlog->count(array('bib' => $bib, 'cp' => $cp)) == 0) {
        // post facebook

        // the incoming time is clock time, not race time, so need to adjust.
        $times = explode(':', $time);
        $flag = true;
        if($bib <= $full_max) { //1 <= full <= 1000
          $type = 'f';
          if($use_clock_time) {
            $time = mydatediff($time, $full_start_time);
          }
        } else if($bib <= $half_max) { // 1001 <= half <= 3000
          $type = 'h';
          if($use_clock_time) {
            $time = mydatediff($time, $half_start_time);
          }
        } else { // 3001 <= mini <== 9999
          $type = 'm';
          if($use_clock_time) {
            $time = mydatediff($time, $mini_start_time);
          }
        }
        syslog(LOG_INFO, "Child " . $my_count . " post FB bib:" . $bib . " cp:" . $cp . " time: " . $time);
        $ret = post_facebook($token, $cp, $runner, $time , $type, $api_response);
        // save response for future use
        if($ret == 200 || $ret == 404) {
          if($ret == 200) {
            syslog(LOG_INFO, "Child " . $my_count . " post FB bib:" . $bib . " cp:" . $cp . " time: " . $time . " successfully");
          } else {
            syslog(LOG_INFO, "Child " . $my_count . " post FB bib:" . $bib . " cp:" . $cp . " got 404");
          }
          $db->fbresponse->insert(array(
              'bib'=>$bib,
              'token'=>$token,
              'cp'=>$cp,
              'time'=>$time,
              'response'=>json_decode($api_response->getBody())
          ));
        } else {
          // mostly, user is not given permission to us
          // TODO: implement some blacklist to avoid repeat sending data to FB
          $db->badlog->insert($doc);
        }
        // save in postlog
        $db->postlog->insert($doc);
      }
      // remove from queue
      $myCol->remove(array('_id' => $doc['_id']));
    } else {
      // take some nap to avoid spin lock
      sleep($worker_child_delay);
    } // if has doc
  } // while
} // if child
?>