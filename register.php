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
        <h1>CMU Marathon Runner Tracker Facebook App</h1>
        <p class="lead">
<?php
  function clean($in)
  {
    return $in;
    /*$t = trim($in);
    $s = strip_tags($t);
    $h = htmlspecialchars($s);
    return $h;*/
  }
  $bib = clean($_POST["bib"]);
  $runner = clean($_POST["runner"]);
  $fbsession = clean($_POST["fbsession"]);
  if(isset($bib) && isset($runner) && isset($fbsession)) {
    $m = new MongoClient();
    $db = $m->cmumarathon;
    $coll = $db->runnertracker;
    $count = $db->runnertracker->count(array('bib' => $bib));
    if($count > 0) {
      ?>
        Error, duplicate bib number
        <?php

    } else {
      $document = array(
                  "bib" => $bib,
                  "runner" => $runner,
                  "fbsession" => $fbsession
                  );
      $ret = $coll->insert($document);
      if(isset($ret) && isset($ret["err"]) && $ret["err"] != NULL) {
        // nothing but lazy
         ?>
          Error, try again. 
          <?php
      } else {
        ?>
          Thanks for registration, see you on the road!
        <?php
      }
    }
  }
?>
</p></div></div>
          </body>
          </html>