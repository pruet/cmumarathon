<?php

$pass =$_GET['pass'];

if(isset($pass) && ($pass == 'hohohohomerryxmas')) {
  $m = new MongoClient();
  $db = $m->cmumarathon;
  echo "<br />registered: " . $db->runnertracker->count();
  echo "<br />total incomming requests: " . $db->runnertlog->count();
  echo "<br />requests waiting for processing: " . $db->runningrequest->count();
  echo "<br />processed requests: " . $db->postlog->count();
  echo "<br />total response from FB: " . $db->fbresponse->count();
} else {
   http_response_code(405);
}