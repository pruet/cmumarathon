<!DOCTYPE html>
<html>
<head>
  <title>CMU Marathon Runner Tracker: Login</title>
  <meta charset="UTF-8">
  <meta name="description" content="Login page for CMU Marathon Runner Tracker Facebook App">
  <meta name="keywords" content="CMU, Marathon, Tracker, Facebook">
  <meta name="author" content="CMU Marathon Technical Team">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<!--   facebook button code -->
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.8";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>

<!-- end facebook button code -->

Please read all the steps before start.<br />
1. Please login with facebook using following link <br />
<div class="fb-login-button"
     data-max-rows="1"
     data-size="medium"
     data-show-faces="true"
     data-auto-logout-link="false"
>
</div>

<?php
/*
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
*/
?>
<br />
2. When Facebook ask for permission, please allow us to post on your wall.<br />
3. After that, please submit your BIB number and name, to be used on the post.<br />
4. Done!.
</body>
</html>
