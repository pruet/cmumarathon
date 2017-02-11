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

$bib = strval($_GET['bib']);

// for dev
header('Access-Control-Allow-Origin: *');  

if(isset($bib)) {
  ?>
    <head>
  <meta property="og:title" content="CMU Marathon 2017 Unofficial Result" />
  <meta property="og:image" content="https://runnerapi.eng.cmu.ac.th/runnertracker/genpngfb.php?bib="<?php echo $bib ?>" />
</head>
<body>
    <img src="https://runnerapi.eng.cmu.ac.th/runnertracker/genpngfb.php?bib=<?php echo $bib ?>" alt="CMU Marathon 2017 Unofficial Result" width=900 height=1200 title="test" />
  <?php
}

?>
</body>
</html>