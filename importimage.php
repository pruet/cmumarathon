<html>
  <head>
 <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
  <!-- Optional theme -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

    <link href="starter-template.css" rel="stylesheet">
    </head>
<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';
if(isset($_POST['submit']) && isset($_POST['pass']) && $_POST['pass'] == $imagefinder_pass) {
  $error = "";
  $target_dir = '/tmp/';
  $target_file = $target_dir . basename($_FILES['fileToUpload']['name']);
  move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $target_file);
  $image_file_type = pathinfo($target_file, PATHINFO_EXTENSION);
  if($image_file_type != 'csv') {
    $error = 'Must be CSV file';
  }

  if($error == "") {
    $m = new MongoClient();
    $db = $m->selectDB($racedb);

    $row = 1;
    $colNum = 4;
    $out = '';
    if(($handle = fopen($target_file, 'r')) !== FALSE) {
      while(($data = fgetcsv($handle, 1000, ',')) != FALSE) {
        if($data[0] == '' || $data[1] == '' || $data[2] == '') {
          continue;
        }
        $bib = intval($data[0]);
        if($bib < 10000 && $bib > 0) { // full/half/mini
          if($bib < 10) {
            $bib = '000' . strval($bib);
          } else if($bib < 100) {
            $bib = '00' . strval($bib);
          } else if($bib < 1000) {
            $bib = '0' . strval($bib);
          }
        } 
        $bib = strval($bib);
        $href = trim(strval($data[1]));
        $url = trim(strval($data[2]));
        $photographer = trim(strval($data[3]));
        if($photographer == "laon") {
          $photographer = "ละอ่อนคนเมือง";
        }
        if($bib != "" && $href != "" && $url != "" && $photographer != "") {
          if($db->runnerimage->count(array('bib'=>$bib, 'url'=>$url)) == 0) {
            $db->runnerimage->insert(array(
              'bib' => $bib,
              'href' => $href,
              'url' => $url,
              'photographer' => $photographer
            ));
            $row++;
          }
        }
      }
      $error =  'Inserted ' . strval($row) . '<br />';
      $db->photographer->remove();
      $photographers = $db->runnerimage->distinct("photographer");
      foreach($photographers as $photographer) {
        $db->photographer->insert(array("name" => $photographer));
      }
    }
    $db->dbinfo->insert(array("update" => date("Y-m-d H:i:s")));
  }
}
?>
  <body>
    <form method="post" enctype="multipart/form-data">
    <div class="jumbotron" id="home">
    <div class="container">
      <?php echo $error ?>
        <div class="row">
          <div class="col-sm-3"></div>
          <div class="col-sm-6">
            <div class="panel panel-primary" id="panelSubmit">
              <div class="panel-heading">
                <h1 class="panel-title">CSV only!!!!!!!!!!!!!!!!!</h1>
              </div>
              <div class="panel-body">
                <input class="form-control" type="text" name="pass" placeholder="Password" />
                <br />
                <input class="form-control" type="file" name="fileToUpload" id="fileToUpload" />
                <br />
                <input class="form-control" type="submit" value="Upload data" name="submit" />
              </div>
            </div>
          </div>
          <div class="col-sm-3"></div>
        </div>
    </div>
    </div>
    </form>
    Example format:
    <pre>
9999,https://www.facebook.com/photo.php?fbid=393403041013280,https://scontent.fbkk2-1.fna.fbcdn.net/v/t31.0-8/16665333_393403041013280_4964422909169669671_o.jpg?oh=d319b6d9e54395fc4d6f7297ce2b04f4&oe=594C46A9,photo1
9998,https://www.facebook.com/photo.php?fbid=393418451011739,https://scontent.fbkk2-1.fna.fbcdn.net/v/t31.0-8/16587277_393418451011739_5884118230187416244_o.jpg?oh=8889c7f821f62320029c639fd14c5c4b&oe=594763BE,photo2
    </pre>
  </body>
</html>
