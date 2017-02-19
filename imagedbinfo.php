<?php
require_once __DIR__ . '/config.php';
// for dev
header('Access-Control-Allow-Origin: *');  
$m = new MongoClient();
$db = $m->cmumarathon;
$result = array();
$all = $db->runnerimage->distinct("url");
//$ua = $db->runnerimage->count(array('bib' => new MongoRegex('/^u/')));
//$result['numimage'] = intval($all) - intval($ua);
$result['numimage'] = count($all);
$bib = $db->runnerimage->distinct("bib");
$ua = $db->runnerimage->distinct('bib', array('bib' => new MongoRegex('/^u/')));
//$result['numimage'] = intval($all) - intval($ua);
$result['numbib'] = intval(count($bib)) - intval($ua);
//{$query: {}, $orderby: {update: -1}}
$doc =  $db->dbinfo->find();
$doc->sort(array('update' => -1));
$doc->limit(1);
$doc->next();
$doc = $doc->current();
$result['updated'] = $doc['update'];
http_response_code(200);
header('Content-type: text/javascript, charset=utf-8');
echo json_encode($result, JSON_PRETTY_PRINT); 
