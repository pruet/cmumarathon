<html>
  <head>
 <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
  <!-- Optional theme -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

    <link href="starter-template.css" rel="stylesheet">
    </head>
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
  $bib = trim($_POST['bib']);
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
<table class="table table-bordered">
<tr><td><h3>Type</h3></td><td><h3>Action</h3></td><td><h3>bib</h3></td><td><h3><?php echo $count ?> Image(s)</h3></td></tr>
<?php
$docs = $db->runnerimagereport->find(array("status"=>"new"));
foreach($docs as $doc) {
  if($doc['action']=='add') {
  echo '<tr><td bgcolor="green"> <h3>Add</h3> ';
  } else {
  echo '<tr><td bgcolor="red"> <h3>Delete</h3> ';
  }
  echo '</td><td><form id="form1" method="post">';
  echo '<input type="hidden" name="id" value="' . $doc['_id'] . '" />';
  echo '<input type="hidden" name="bib" value="' . $doc['bib'] . '" />';
  echo '<input type="hidden" name="url" value="' . $doc['url'] . '" />';
  echo '<input type="hidden" name="pass" value="idontlikepopmusic" />';
  echo '<input type="hidden" name="action" value="' . $doc['action'] . '" />';

  echo ' <button class="btn-block btn-lg btn-success btn" type="submit" form="form1" name="submit" value="Confirm">Confirm</button><br />';
  echo '<button class="btn-block btn-lg btn-danger btn" form="form1" type="submit" name="submit" value="Reject">Reject</button> ';
  echo '</form>';
  echo '</td><td><h2> ';
  echo $doc['bib'];
  echo ' </h2></td><td><img width=800 src="';
  echo $doc['url'];
  echo '" /></td>';
  echo '</tr>';
}
?>
</table>
</div>
</body>
</html>
