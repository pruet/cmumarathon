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
<html>
  <body>
    <div class="jumbotron" id="home">
      <div class="container" >
        <?php echo $error ?>
        <form method="post" enctype="multipart/form-data">
          <div class="row">
            <div class="col-sm-8">
              <div class="panel panel-primary collapse" id="panelSubmit">
                <div class="panel-heading">
                  <h1 class="panel-title">CSV only!!!!!!!!!!!!!!!!!</h1>
                </div>
                <div class="panel-body">
                  <input class="form-control" type="file" name="fileToUpload" id="fileToUpload" />
                  <input class="form-control" type="text" name="pass" placeholder="Password" />
                  <input class="form-control" type="submit" value="Upload data" name="submit" />
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </body>
</html>