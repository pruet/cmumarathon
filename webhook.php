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

function str_starts_with($haystack, $needle)
{
  return strpos($haystack, $needle) === 0;
}

if(!session_id()) {
  session_start();
}
if(!openlog($syslogid, LOG_CONS | LOG_PID | LOG_PERROR, LOG_LOCAL7)) {
  echo "Can't open syslog, send message to console";
}

// Check request type
$type = clean($_GET["type"]);
if($type == 'json') {
  $json = file_get_contents('php://input');
  if(!str_starts_with($json, "{")) {
    $items = explode("&", $json);
    $out = "{";
    foreach($items as $item) {
      $it = explode("=", $item);
      if($it[0] == "bib") {
        $it[1] = substr($it[1], 5);
      }
      $out = $out . '"' . $it[0] . '":"' . $it[1] . '",';

    }
    $json = $out . '"x":"y"}';
  }
  syslog(LOG_INFO, "|" . strval($json) . "|");
  $js = json_decode($json);
  
  $bib = clean($js->{'bib'});
  $cp = clean($js->{'cp'});
  $time = clean($js->{'time'});
  $pass = clean($js->{'tk'});
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

// check if user (bib) want to post at this location (cp)
  $query = array('bib' => $bib, $cp => 'on');
  if(($doc = $db->runnertracker->findOne(array('bib' => $bib, $cp => 'on'))) != NULL) {
    // check that we never post it before
    if($db->postlog->findOne(array('bib' => $bib, 'cp'=> $cp)) == NULL) {
      $query = array(
        'bib' => $bib,
        'cp' => $cp,
        'time' => $time,
        'token' => $doc["fbsession"],
        'runner' => $doc["runner"]
      );
      // add request to db
      $db->runnerrequest->insert($query); // this will be removed by worker
      $db->runnerlog->insert($query); // this will be permanent
    }
  }
}
