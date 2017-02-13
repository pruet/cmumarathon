<?php
require_once __DIR__ . '/config.php';

if(!session_id()) {
  session_start();
}
if(!openlog($syslogid, LOG_CONS | LOG_PID | LOG_PERROR, LOG_LOCAL7)) {
  echo 'Can\'t open syslog, send message to console';
}

// Check request type
$type = $_GET["type"];
if($type == 'json') {
  $json = file_get_contents('php://input');
  // Hack for KKU team sending in URL paramter format bib=xxx&cp==yyy&...
  // so, we convert it to json
  // TODO: might need to remove it next time;
  if(starpos($json, '{') != 0) {
    $items = explode('&', $json);
    $out = '{';
    foreach($items as $item) {
      $it = explode('=', $item);
      if($it[0] == 'bib') {
        // they send in 20M20xxxx format, we need only xxxx
        $it[1] = substr($it[1], 5);
      }
      $out = $out . '"' . $it[0] . '":"' . $it[1] . '",';
    }
    // lazy padding
    $json = $out . '"x":"y"}';
  }
  // end hack
  syslog(LOG_INFO, "|" . strval($json) . "|");
  $js = json_decode($json);
  
  $bib = $js->{'bib'};
  $cp = $js->{'cp'};
  $time = $js->{'time'};
  $pass = $js->{'tk'};
} else {
  $bib = clean($_POST["bib"]);
  $cp = clean($_POST["cp"]);
  $time = clean($_POST["time"]);
  $pass = clean($_POST["tk"]);
}

// Check parameters
if(isset($pass) && ($pass == $webhook_pass) && isset($bib) && isset($cp) && isset($time)) {
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
