<html>
<body>
<?php
require_once __DIR__ . '/config.php';

$m = new MongoClient();
$db = $m->selectDB($racedb);

$count = $db->runnerimage->distinct("url");
?>
total = <?php echo count($count) ?> images <br />
List of photographers:
<ul>
<?php
$pho = $db->runnerimage->distinct("photographer");
foreach($pho as $p)
{
  echo '<li>' . $p . '</li>';
}
?>
</ul>
<table border=1>
<tr><td>BIB</td><td>count</td><td>case</td></tr>
<?php
$docs = $db->runnerimage->distinct('bib');
sort($docs);
foreach($docs as $doc) {
  $dlen = strlen($doc);
  if($dlen != 4 && $dlen != 6 && $doc[0] != 'u') {
    $count = $db->runnerimage->count(array("bib"=>$doc));
    echo '<tr><td><a href="https://runnerapi.eng.cmu.ac.th/runnertracker/imagesearch.html?bib=' . $doc . '">' . $doc . '</a></td><td>' . $count . '</td><td> bib length</td></tr>';
  } else if($dlen == 4) {
    if($db->runnerinfo->count(array("bib" => intval($doc))) == 0) {
      $count = $db->runnerimage->count(array("bib"=>$doc));
      echo '<tr><td><a href="https://runnerapi.eng.cmu.ac.th/runnertracker/imagesearch.html?bib=' . $doc . '">' . $doc . '</a></td><td>' . $count . '</td><td>bib not in db</td></tr>';
    }
  }
}
?>
</table>
</body>
</html>
