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
    echo $access_token;
  }
}
/*$access_token = 'EAAOrDZA2VPWYBAE04fAVZCyDFuE8ZCr8vZBd97M7YAe0ZA3PnIWPoLvTPHVZCzEJtWZCpZC4lXddAtAeNLwP8kBNpcIByv0zmeCplZCCZApohuaHYvxKrMZBI1fSZAikts5604V6cD8ZB9zPXsRdujknqcZCq9xV8klMuXGfHjXzKAWO3OkwZDZD';

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
  try {
    $photoCaption = 'My photo caption';
    $post_data = array(
      'message' => $photoCaption,
      'url' => 'https://runnerapi.eng.cmu.ac.th/runnertracker/images/badge-c1.png'
    );
    $apiResponse = $fb->post('/me/photos', $post_data);
  } catch (FacebookApiException $e) {
    $user = null;
    print_r($e);
  }
} else {
  echo "no user";
}*/
?>
<table>
<form method="post" action="/runnertracker/post-update.php" >
  <tr><td>BIB:</td><td><input type="text" name="bib" /></td></tr>
  <tr><td>CP:</td><td><select><option value="1">1</option><option value="2">2</option><option value="3">3</option></select></td></tr>
  <tr><td>CP:</td><td><input type="text" name="cp" /></td></tr>
  <tr><td>time:</td><td><input type="text" name="time" /></td></tr>
  <tr><td><input type="submit" /></td><td><td></tr>
</form>
</table>
<body>
<html>
