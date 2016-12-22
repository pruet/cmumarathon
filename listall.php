<?php
    $m = new MongoClient();
    $db = $m->cmumarathon;
    $coll = $db->runnertracker;
?>

<html>
<body>
<table>
<th> <td>BIB</td><td>Name</td><td>FB Session</td></th>
<?php
  $cur = $coll->find();
  foreach($cur as $document) {
    echo "<tr>";
    echo "<td>" . $document["bib"] . "</td>";
    echo "<td>" . $document["runner"] . "</td>";
    echo "<td>" . $document["fbsession"] . "</td>";
    echo "</tr>";
  }
?>
</table>
</body>
</html>