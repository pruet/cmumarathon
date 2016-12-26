<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

if(!session_id()) {
  session_start();
}

$access_token = 'EAAOrDZA2VPWYBAE04fAVZCyDFuE8ZCr8vZBd97M7YAe0ZA3PnIWPoLvTPHVZCzEJtWZCpZC4lXddAtAeNLwP8kBNpcIByv0zmeCplZCCZApohuaHYvxKrMZBI1fSZAikts5604V6cD8ZB9zPXsRdujknqcZCq9xV8klMuXGfHjXzKAWO3OkwZDZD';

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
    $apiResponse = $facebook->post('/me/photos', $post_data);
  } catch (FacebookApiException $e) {
    $user = null;
    print_r($e);
  }
} else {
  echo "no user";
}
?>