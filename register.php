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
    //TODO block duplicate BIB
    $m = new MongoClient();
    $db = $m->cmumarathon;
    $coll = $db->runnertracker;
    $document = array(
                 "bib" => $bib,
                 "runner" => $runner,
                 "fbsession" => $fbsession
                 );
    $ret = $coll->insert($document);
    if(isset($ret) && isset($ret["err"]) && $ret["err"] != NULL) {
      // nothing but lazy
      ?>
        <html>
        <body>
        Error, try again. 
        </body>
        </html>
        <?php
    } else {
      ?>
        <html>
        <body>
        Thanks for registration, see you on the road!
        </body>
        </html>
      <?php
    }
  }
?>