<?php
require_once __DIR__ . '/config.php';
// for dev
header('Access-Control-Allow-Origin: *');  
$m = new MongoClient();
$db = $m->cmumarathon;
$result = array();
$all = $db->runnerimage->count();
$ua = $db->runnerimage->count(array('bib' => new MongoRegex('/^u/')));
$result['numimage'] = intval($all) - intval($ua);
$bib = $db->runnerimage->distinct("bib");
$result['numbib'] = count($bib);
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
