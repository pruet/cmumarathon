<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

if(!session_id()) {
  session_start();
}

$fb = new Facebook\Facebook([
  'app_id' => $app_id,
  'app_secret' => $app_secret,
  'default_graph_version' => $default_graph_version,
]);

$accessToken = $_POST['accessToken'];
$debug = $_GET['debug'];

if (isset($accessToken)) {
  $oAuth2Client = $fb->getOAuth2Client();

// Exchanges a short-lived access token for a long-lived one
  $longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
  // Logged in!
  $_SESSION['facebook_access_token'] = (string) $longLivedAccessToken;
  // Now you can redirect to another page and use the
  // access token from $_SESSION['facebook_access_token']
  try {
    $response = $fb->get('/me?fields=id,name', $accessToken);
    $user = $response->getGraphUser();
  } catch(Facebook\Exceptions\FacebookSDKException $e) {
    $user = array("name" => "none", "id" => "none");
  }
} else if($debug != 'true') {
  // sned back to login
  header('Location: https://cmumarathon.com/fblogin.html');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>CMU Marathon Runner Tracker: Login</title>
  <meta charset="UTF-8">
  <meta name="description" content="Login page for CMU Marathon Runner Tracker Facebook App">
  <meta name="keywords" content="CMU, Marathon, Tracker, Facebook">
  <meta name="author" content="CMU Marathon Technical Team">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
  <script src="jquery.redirect.js"></script>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
  <!-- Optional theme -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

    <link href="starter-template.css" rel="stylesheet">
  <!-- Latest compiled and minified JavaScript -->
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
  <script>
    function checkAll() {
          document.getElementById("locationCS").checked = true;
          document.getElementById("locationC1").checked = true;
          document.getElementById("locationC2").checked = true;
          document.getElementById("locationC3").checked = true;
          document.getElementById("locationC4").checked = true;
          document.getElementById("locationCF").checked = true;
    }
    function checkNone() {
          document.getElementById("locationCS").checked = false;
          document.getElementById("locationC1").checked = false;
          document.getElementById("locationC2").checked = false;
          document.getElementById("locationC3").checked = false;
          document.getElementById("locationC4").checked = false;
          document.getElementById("locationCF").checked = false;
    }
    $(document).ready(function(){
      $('#biblookup').click(function(){
        var bib = $('#bib').val();
        if(bib != undefined && bib != null && bib != "") {
          var query = 'https://runnerapi.eng.cmu.ac.th/runnertracker/biblookup.php?bib='.concat(bib).concat('&pass=hohohohomerryxmas');
          var jqxhr = $.getJSON(query, function(data) {
          }).done(function(data) {
            $('#bibsuccess').collapse('show'); 
            var content = "<strong>First Name</strong> " + data.fname + "<br /><strong>Last name</strong> " + data.lname + "<br /><strong>Race type</strong> ";
            if(data.type == "f") {
              content = content + "Marathon";
            } else if(data.type == "h") {
              content = content + "Half marathon";
              $('#lc3').hide();
              $('#lc4').hide();
            } else {
              content = content + "Mini marathon";
              $('#lc1').hide();
              $('#lc3').hide();
              $('#lc4').hide();
            }
            content = content + "<br /><strong>If the above information is correct, please proceed to the next step"
            $('#bibsuccess').html(content); 
            $('#bibalert').collapse('hide'); 
            $('#panelName').collapse('show'); 
            $('#panelMarker').collapse('show'); 
            $('#panelSubmit').collapse('show'); 
          }).fail(function(XMLHttpRequest, textStatus, errorThrown) {
            $('#bibalert').collapse('show'); 
            $('#bibsuccess').collapse('hide'); 
            $('#panelName').collapse('hide'); 
            $('#panelMarker').collapse('hide'); 
            $('#panelSubmit').collapse('hide'); 
          });
        }
      });
      $('#bibsearch').click(function(){
        var txt = $('#searchtxt').val();
        if(txt != undefined && txt != null && txt != "") {
          var query = 'https://runnerapi.eng.cmu.ac.th/runnertracker/bibfinder.php?txt='.concat(txt).concat('&pass=hohohohomerryxmas');
          var jqxhr = $.getJSON(query, function(data) {
          }).done(function(data) {
            var content = "";
            for (var i in data) {
              content = content + "<div class=\"panel panel-default\"><div class=\"panel-heading\"><strong>First Name</strong> " + data[i].fname + " <strong>Last name</strong> " + data[i].lname + "</div><div class=\"panel-body\"><strong>Bib number</strong> " + data[i].bib + "</div></div>";
            }
            $('#searchsuccess').html(content); 
            $('#searchsuccess').collapse('show'); 
            $('#searchalert').collapse('hide'); 
          }).fail(function(XMLHttpRequest, textStatus, errorThrown) {
            $('#searchalert').collapse('show'); 
            $('#searchsuccess').collapse('hide'); 
          });
        }
      });
      $('#runnername').on('change keyup keypress', function(){
        var runnernanme = $('#runnername').val();
        if(runnername != "") {
           $(':input[type="submit"]').prop('disabled', false);
        }
      });
      $('#runnerform').on('keyup keypress', function(e) {
          var keyCode = e.keyCode || e.which;
          if (keyCode === 13) { 
            e.preventDefault();
            return false;
        }
      });
    });
  </script>
</head>
<body>
      <div class="jumbotron" id="home">
      <div class="container" >
    <h1>CMU Marathon Runner Tracker Facebook App</h1>
    <p class="lead">
      Please provide your bib number, name and when you want to publish your progress. The name will be shown on the badge posted on your Facebook wall. If you have already provided the information, you are all set, please close this browser window and see you on the race day!. If you don't know your bib number, you can <button type="button" class="btn btn-info" data-toggle="modal" data-target="#bibModal">search for it</button>
    </p>
    <div class="modal fade" id="bibModal" role="dialog">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Search for a bib</h4>
          </div>
          <div class="modal-body">
              <p>
              <input type="text" name="searchtxt" id="searchtxt" class="form-control" required placeholder="Please provide your first name, last name, Thai citizen ID or your passport ID" />
              <input type="button" value="Search" id="bibsearch"/>
              </p>
              <p>
              <div class="alert alert-danger collapse" role="alert" id="searchalert">Runner not found</div>
              <div class="alert alert-success collapse" role="alert" id="searchsuccess"></div>
              </p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
<form method="post" id="runnerform" action="/runnertracker/register.php">
  <input type="hidden" name="fbsession" value="<?php echo (string)$longLivedAccessToken ?>" />
  <input type="hidden" name="fbid" value="<?php echo (string)$user['id'] ?>" />
   <div class="row">
        <div class="col-sm-8">
          <div class="panel panel-primary">
            <div class="panel-heading">
              <h1 class="panel-title" class="control-label">1. Please provide your bib number</h1>
            </div>
            <div class="panel-body">
              <p>
              <input type="text" maxlength="4" name="bib" id="bib" class="form-control" required placeholder="Only last four digit of your bib, e.g., if your bib is 18M1234, please input only 1234." />
              <input type="button" value="Check bib" id="biblookup"/>
              </p>
              <p>
              <div class="alert alert-danger collapse" role="alert" id="bibalert">Bib not found</div>
              <div class="alert alert-success collapse" role="alert" id="bibsuccess"></div>
              </p>
            </div>
          </div>
        </div>
     </div>
     <div class="row">
        <div class="col-sm-8">
          <div class="panel panel-primary collapse" id="panelName">
            <div class="panel-heading">
              <h1 class="panel-title" class="control-label">2. Please provide your name</h1>
            </div>
            <div class="panel-body">
              <input type="text" id="runnername" name="runner" maxlength="10" class="form-control" placeholder="This name will be shown on the Facebook post, at most 10 characters." required />
            </div>
          </div>
        </div>
     </div>
     <div class="row">
       <div class="col-sm-8">
         <div class="panel panel-primary collapse" id="panelMarker">
           <div class="panel-heading">
             <h1 class="panel-title">3. When do you want to publish your progress on your facebook wall ?</h1>
           </div>
          <div class="panel-body">
            <div class="checkbox">
              <label id="lcs"><input id="locationCS" type="checkbox" name="locationCS" checked />When I'm at the start line</label><br />
              <label id="lc1"><input id="locationC1" type="checkbox" name="locationC1" checked />When I pass CP1</label><br />
              <label id="lc2"><input id="locationC2" type="checkbox" name="locationC2" checked />When I pass CP2</label><br />
              <label id="lc3"><input id="locationC3" type="checkbox" name="locationC3" checked />When I pass CP3</label><br />
              <label id="lc4"><input id="locationC4" type="checkbox" name="locationC4" checked />When I pass CP4</label><br />
              <label id="lcf"><input id="locationCF" type="checkbox" name="locationCF" checked />When I pass the finish line</label><br />
              <button type="button" id="locationAll" class="btn btn-default" onClick="checkAll()">Check all</button>
              <button type="button" id="locationClear" class="btn btn-default" onClick="checkNone()">Clear all</button>
            </div>
          </div>
        </div>
       </div>
     </div>
     <div class="row">
       <div class="col-sm-8">
         <div class="panel panel-primary collapse" id="panelSubmit">
           <div class="panel-heading">
             <h1 class="panel-title">4. Please confirm all information before submitting</h1>
           </div>
          <div class="panel-body">
          <input type="submit" id="submitbutton" disabled/>
          </div>
        </div>
      </div>
     </div>
</table>
</body>
</html
