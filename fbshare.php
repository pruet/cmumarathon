<html>
<?php
$bib = strval($_GET['bib']);

// for dev
//header('Access-Control-Allow-Origin: *');

if(isset($bib)) {
  ?>
  <head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# website: http://ogp.me/ns/website#">
  <meta property="og:type" content="website" />
  <meta property="og:url" content="https://runnerapi.eng.cmu.ac.th/runnertracker/fbshare.php?bib=<?php echo $bib ?>" />
  <meta property="og:title" content="CMU Marathon 2017 Unofficial Result" />
  <meta property="og:site_name" content="CMU Marathon 2017 Unofficial Result" />
  <meta property="og:image" content="https://runnerapi.eng.cmu.ac.th/runnertracker/genpngfb.php?bib=<?php echo $bib ?>" />
  <meta property="og:image:width" content="450" />
  <meta property="og:image:height" content="600" />
  <meta property="og:description" content="Congratulations and thank you for joining us this year, hope we will see you at CMU Marathon 2018!" />
</head>
<body>
<center><h3><a href="https://marathon.eng.cmu.ac.th/AllResult">CMU Marathon 2017 Unofficial Result</a></h3><br />
    <img src="https://runnerapi.eng.cmu.ac.th/runnertracker/genpngfb.php?bib=<?php echo $bib ?>" alt="CMU Marathon 2017 Unofficial Result" title="CMU Marathon 2017 Unofficial Result" />
  <?php
}

?>
</body>
</html>