<html>
<body>
<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

function clean($in)
{
  return $in;
  $t = trim($in);
  $s = strip_tags($t);
  $h = htmlspecialchars($s);
  return $h;
}

$bib = intval($_GET['bib']);

// for dev
header('Access-Control-Allow-Origin: *');  

if(isset($bib)) {
  $m = new MongoClient();
  $db = $m->cmumarathon;
  ?>
    <img src="https://runnerapi.eng.cmu.ac.th/runnertracker/genpng.php?name=test&time=1:2:3&pace=1%272%22&cp=3" />
  <?
}

?>
</body>
</html>