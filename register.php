<?php
  function clean($in)
  {
    $h = trim($in);
    $h = strip_tags($h);
    $h = htmlspecialchars($h);
    return $h;
  }
  $bib = clean($_POST['bib']);
  $runner = clean($_POST['runner']);
  $fbsession = clean($_POST['fbsession']);
  $fbid = clean($_POST['fbid']);
  $locationCS = $_POST['locationCS'];
  $locationC1 = $_POST['locationC1'];
  $locationC2 = $_POST['locationC2'];
  $locationC3 = $_POST['locationC3'];
  $locationC4 = $_POST['locationC4'];
  $locationCF = $_POST['locationCF'];
  if(isset($bib) && isset($runner) && isset($fbsession)) {
    $m = new MongoClient();
    $db = $m->cmumarathon;
    $coll = $db->runnertracker;
    $count = $db->runnertracker->count(array('bib' => $bib));
    if($count > 0) {
       $status = "Error, duplicate bib number.";
    } else {
      $document = array(
                  'bib' => $bib,
                  'runner' => $runner,
                  'fbsession' => $fbsession,
                  'fbid' => $fbid,
                  's' => $locationCS,
                  '1' => $locationC1,
                  '2' => $locationC2,
                  '3' => $locationC3,
                  '4' => $locationC4,
                  'f' => $locationCF,
                  );
      $ret = $coll->insert($document);
      if(isset($ret) && isset($ret["err"]) && $ret["err"] != NULL) {
          $status = "Error, try again.";
      } else {
          $status = "Thanks for registration, see you on the road!";
      }
    }
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>CMU Marathon Runner's Track Me: Register</title>
  <meta charset="UTF-8">
  <meta name="description" content="Register Page for CMU Marathon Runner's Track Me Facebook App">
  <meta name="keywords" content="CMU, Marathon, Track Me, Tracker, Facebook">
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
        <h1>CMU Marathon Runner's Track Me Facebook App</h1>
        <p class="lead">
        <?php 
          echo $status; 
        ?>
        </p></div></div>
          </body>
          </html>
