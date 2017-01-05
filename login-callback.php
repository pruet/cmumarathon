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

/*$_SESSION['FBRLH_state']=$_GET['state'];
$helper = $fb->getRedirectLoginHelper();
try {
  $accessToken = $helper->getAccessToken();
} catch(Facebook\Exceptions\FacebookResponseException $e) {

  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}*/

$accessToken = $_COOKIE['accessToken'];

if (isset($accessToken)) {
  $oAuth2Client = $fb->getOAuth2Client();

// Exchanges a short-lived access token for a long-lived one
  $longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
  // Logged in!
  $_SESSION['facebook_access_token'] = (string) $longLivedAccessToken;
  // Now you can redirect to another page and use the
  // access token from $_SESSION['facebook_access_token']
}
?>
<html>
<head>
</head>
<body>
Please provide us your BIB number and name.
<table>
<form method="post" action="/runnertracker/register.php" >
  <input type="hidden" name="fbsession" value="<?php echo (string)$longLivedAccessToken ?>" />
  <tr><td>BIB:</td><td><input type="text" name="bib" /></td></tr>
  <tr><td>Name:</td><td><input type="text" name="runner" /></td></tr>
  <tr><td><input type="submit" /></td><td><td></tr>
</form>
</table>
</body>
</html
