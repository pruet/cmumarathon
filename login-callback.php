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

$accessToken = $_POST['accessToken'];

if (isset($accessToken)) {
  $oAuth2Client = $fb->getOAuth2Client();

// Exchanges a short-lived access token for a long-lived one
  $longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
  // Logged in!
  $_SESSION['facebook_access_token'] = (string) $longLivedAccessToken;
  // Now you can redirect to another page and use the
  // access token from $_SESSION['facebook_access_token']
} else {
  // sned back to login
  header('Location: https://runnerapi.eng.cmu.ac.th/runnertracker/login');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>CMU Marathon Runner Tracker: Login</title>
  <meta charset="UTF-8">
  <meta name="description" content="Login page for CMU Marathon Runner Tracker Facebook App">
  <meta name="keywords" content="CMU, Marathon, Tracker, Facebook">
  <meta name="author" content="CMU Marathon Technical Team">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
  <script src="jquery.redirect.js"></script>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
  <!-- Optional theme -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

    <link href="starter-template.css" rel="stylesheet">
  <!-- Latest compiled and minified JavaScript -->
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</head>
<body>
      <div class="jumbotron" id="home">
      <div class="container" >
        <h1>Welcome to CMU Marathon Runner Tracker Facebook App</h1>
        <p class="lead">
          Please provide your bib number and name. The name will be shown on the badge post on your Facebook wall. 
       </p>
<form method="post" action="/runnertracker/register.php" >
  <input type="hidden" name="fbsession" value="<?php echo (string)$longLivedAccessToken ?>" />
   <div class="row">
        <div class="col-sm-4">
          <div class="panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">Bib number</h3>
            </div>
            <div class="panel-body">
              <input type="text" name="bib" />
            </div>
          </div>
        </div>
        <div class="col-sm-4">
          <div class="panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">Name</h3>
            </div>
            <div class="panel-body">
              <input type="text" name="runner" />
            </div>
          </div>
        </div>
        </div>
  <input type="submit" />
</table>
</body>
</html
