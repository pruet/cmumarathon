<html>
<body>
<?php
require_once __DIR__ . '/config.php';

if($_GET['pass'] != 'idontlikepopmusic' && $_POST['pass'] != 'idontlikepopmusic') {
return;
}
$m = new MongoClient();
$db = $m->cmumarathon;

if($_POST['submit'] == 'Reject') {
  $bib = $_POST['bib'];
  $url = $_POST['url'];
  $action = $_POST['action'];
  $id = new MongoId($_POST['id']);
  $db->runnerimagereport->update(
    array('_id'=>$id), 
    array('$set' => array('status'=>'reject'))
  );
} else if($_POST['submit'] == 'Confirm') {
  $bib = $_POST['bib'];
  $url = $_POST['url'];
  $action = $_POST['action'];
  $id = new MongoId($_POST['id']);
  if($action == 'add') {
    $doc = $db->runnerimage->findOne(array('url' => $url));
    $doc['bib'] = $bib;
    unset($doc['_id']);
    if($db->runnerimage->findOne(array('bib'=>$bib, 'url' => $url)) == NULL) {
      $db->runnerimage->insert($doc);
    }
  } else if($action == 'delete') {
    $ubib = 'u' . $bib;
    $db->runnerimage->update(
      array('bib' => $bib, 'url' => $url),
      array('$set' => array ('bib' => $ubib))
    );
  }
  $db->runnerimagereport->update(
    array('_id'=>$id), 
    array('$set' => array('status'=>'confirm'))
  );
}


$count = $db->runnerimagereport->count(array("status"=>"new"));
?>
total = <?php echo $count ?> images <br />
</ul>
<table border=1>
<tr><td>Type</td><td>Action</td><td>bib</td><td>Image</td></tr>
<?php
$docs = $db->runnerimagereport->find(array("status"=>"new"));
foreach($docs as $doc) {
  if($doc['action']=='add') {
  echo '<tr><td bgcolor="green">';
  } else {
  echo '<tr><td bgcolor="red">';
  }
  echo $doc['action'];
  echo '</td><td><form method="post">';
  echo '<input type="hidden" name="id" value="' . $doc['_id'] . '" />';
  echo '<input type="hidden" name="bib" value="' . $doc['bib'] . '" />';
  echo '<input type="hidden" name="url" value="' . $doc['url'] . '" />';
  echo '<input type="hidden" name="pass" value="idontlikepopmusic" />';
  echo '<input type="hidden" name="action" value="' . $doc['action'] . '" />';

  echo '<input type="submit" name="submit" value="Confirm" /><br /><br /><input type="submit" name="submit" value="Reject" />';
  echo '</form>';
  echo '</td><td>';
  echo $doc['bib'];
  echo '</td><td><img width=800 src="';
  echo $doc['url'];
  echo '" /></td>';
  echo '</tr>';
}
?>
</table>
</body>
</html>
