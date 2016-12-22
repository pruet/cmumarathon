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
  $fbsession = clean($POST["fbsession"]);

  if(isset($bib) && isset($runner) && isset($fbsession)) {
    $m = new MongoClient();
    $db = $m->cmumarathon;
    $coll = $db->runnertracker;
    $document = array(
                 "bib" => $bib,
                 "runner" => $runner,
                 "fbsession" => $fbsession
                 );
    $ret = $coll->insert($document);
    var_dump($ret);
  }
?>
should be ok