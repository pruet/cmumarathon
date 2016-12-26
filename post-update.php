<html>
<body>
<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

if(!session_id()) {
  session_start();
}
function clean($in)
{
  return $in;
  /*$t = trim($in);
  $s = strip_tags($t);
  $h = htmlspecialchars($s);
  return $h;*/
}
$bib = clean($_POST["bib"]);
$cp = clean($_POST["cp"]);
$time = clean($_POST["time"]);
if(isset($bib) && isset($cp) && isset($time)) {
  $m = new MongoClient();
  $db = $m->cmumarathon;
  $coll = $db->runnertracker;
  $query = array('bib' => $bib);
  $cursor = $coll->find($query);
  foreach($cursor as $doc) {
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
      $pace = "8'7\"/km";
      $image_base = 'https://runnerapi.eng.cmu.ac.th/runnertracker/genpng.php';
      $image_query = 'cp=' . urlencode($cp) . '&name=' . urlencode($name) . '&time=' . urlencode($time) . '&pace=' . urlencode($pace);
      $image = $image_base . '?' . htmlentities($image_query);
      try {
        $photoCaption = 'My photo caption';
        $post_data = array(
          'message' => $photoCaption,
          'url' => $image
        );
        $apiResponse = $fb->post('/me/photos', $post_data);
        break;
      } catch (FacebookApiException $e) {
        $user = null;
        print_r($e);
      }
    } else {
      echo "no user";
    }
  }
}
?>
<table>
<form method="post" action="/runnertracker/post-update.php" >
  <tr><td>BIB:</td><td><input type="text" name="bib" /></td></tr>
  <tr><td>CP:</td><td>
  <select name="cp">
  <option value="1">Checkpoint 1</option>
  <option value="2">Checkpoint 2</option>
  <option value="3">Checkpoint 3</option>
  <option value="f">Finished</option>
  </select></td></tr>
  <tr><td>time:</td><td><input type="text" name="time" /></td></tr>
  <tr><td><input type="submit" /></td><td><td></tr>
</form>
</table>
<body>
<html>
