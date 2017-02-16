<html>
<body>
<table border=1>
<tr><td>BIB</td><td>count</td></tr>
<?php
require_once __DIR__ . '/config.php';

$m = new MongoClient();
$db = $m->cmumarathon;

$docs = $db->runnerimage->distinct("bib");
foreach($docs as $doc) {
  $count = $db->runnerimage->count(array("bib"=>$doc));
  echo "<tr><td>" . $doc . "</td><td>" . $count . "</td></tr>\n";
}
?>
</table>
</body>
</html>
