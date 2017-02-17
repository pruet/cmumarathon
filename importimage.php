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
if(isset($_POST['submit'])) {
  $error = "";
  $target_dir = 'uploads/';
  $target_file = $target_dir . basename($_FILES['fileToUPload']['name']);
  $imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);

  if($imageFileType != 'csv') {
    $error = 'Must be CSV file';
  }

  if($error == "") {
    $m = new MongoClient();
    $db = $m->cmumarathon;

    $row = 1;
    $colNum = 4;
    $out = '';
    if(($handle = fopen($target_file, 'r')) !== FALSE) {
      while(($data = fgetcsv($handle, 1000, ',')) != FALSE) {
        $bib = intval($data[0]);
        if($bib < 10000) { // full/half/mini
          if($bib < 10) {
            $bib = '000' . strval($bib);
          } else if($bib < 100) {
            $bib = '00' . strval($bib);
          } else if($bib < 1000) {
            $bib = '0' . strval($bib);
          }
        } 
        $bib = strval($bib);
        $href = strval($data[1]);
        $url = strval($data[2]);
        $photographer = strval($data[3]);
        $db->runnerimage->insert(array(
          'bib' => $bib,
          'href' => $href,
          'url' => $url,
          'photographer' => $photographer
        ));
        $row++;
      }
      $error =  'Inserted ' . strval($row) . '<br />';
      $photographers = $db->runnerimage->distinct("photographer");
      $db->photographer->remove();
      foreach($photographers as $photographer) {
        $db->photographer->insert(array("name" => $photographer));
      }
    }
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
  </body>
</html>