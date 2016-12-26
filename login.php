<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

if(!session_id()) {
  session_start();
}

$fb = new Facebook\Facebook([
  'app_id' => $app_id,
  'app_secret' => $app_secret,
  'default_graph_version' => $default_graph_version,
]);

$helper = $fb->getRedirectLoginHelper();
$_SESSION['FBRLH_state']=$_GET['state'];
$permissions = ['email', 'publish_actions', 'user_photos']; // optional
$loginUrl = $helper->getLoginUrl('https://runnerapi.eng.cmu.ac.th/runnertracker/login-callback.php', $permissions);

echo '<a href="' . $loginUrl . '">Log in with Facebook!</a>';

?>
