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
        echo "Progress posted.<br />";
        // save to log
        $db->postlog->insert(array('bib' => $bib, $cp=>'on'));
      }
    } catch (FacebookApiException $e) {
      $user = null;
      return 500;
    }
  } else {
    return 404;
  }
  return 200;
}

class Worker extends Threaded {
  protected $complete;

  public function __construct($doc) 
  {
    $this->complete = false;
    $this->doc = $doc;
  }

  public function run()
  {
    $this->out = $this->doc['bib'];
    $this->complete = true;
  }

  public function isGarbage()
  {
    return $this->complete;
  }
}

class WorkerPool extends Pool
{
  public function process()
  {
    while(count($this->work)) {
      $this->collect(function (Worker $task) {
        if($task->isGarbage()) {
          $tmpObj = new stdclass();
          $tmpObj->complete = $task->complete;
          $this->data[] = $tmpObj;
        }
        return $task->isGarbage(); 
      });
    }
    $this->shutdown();
    return $this->data;
  }
}
// query mango db for lastest update

$m = new MongoClient();
$db = $m->cmumarathon;

// get 100 req from request
while(true) {
  echo "Next round<br/>";
  $pool = new WorkerPool(10);
  if(($docs = $db->request->find()->limit(100)) != NULL) {
    foreach($docs as $doc) {
      $pool->submit(new Worker($doc));
    } 
    $retAttr = $pool->process();
  }
  print_r($retArr);
  usleep(30000000);
}
// get new thread from thread pool

// send query to fb

// update  request table


// Check request type

?>