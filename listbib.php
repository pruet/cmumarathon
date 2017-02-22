<html>
<body>
<?php
require_once __DIR__ . '/config.php';

$m = new MongoClient();
$db = $m->cmumarathon;

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
<tr><td>BIB</td><td>count</td></tr>
<?php
$docs = $db->runnerimage->distinct('bib');
sort($docs);
foreach($docs as $doc) {
  if($doc[0] != 'u') {
    $count = $db->runnerimage->count(array("bib"=>$doc));
    echo '<tr><td><a href="https://runnerapi.eng.cmu.ac.th/runnertracker/imagesearch.html?bib=' . $doc . '">' . $doc . '</a></td><td>' . $count . '</td></tr>';
  }
}
?>
</table>
</body>
</html>
