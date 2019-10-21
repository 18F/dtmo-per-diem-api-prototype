<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

$app = new \Slim\App;

function parseDataFile($file){
  $data = file_get_contents($file);
  $data = str_replace(";", ",", $data);
  return array_map("str_getcsv", explode(PHP_EOL, $data));
}

function getDataFilePath($conusOconus, $incAll, $incMil){
  $perDiemTypes = [
    "conus" => [
      "e_connow",   /* Includes all Cities, Towns, Counties, DOD Installations, and GSA Defined Per Diem Locations. */
      "e_conusnm",  /* Excludes DOD Installations */
      "connow",     /* Includes Counties, DOD Installations, and GSA Defined Per Diem Locations */
      "conusnm",    /* Includes Counties and GSA Defined Per Diem Locations. Excludes DOD Installations */
      "conusmil"    /* DOD Installations only */
    ],
    "oconus" => [
      "oconus",     /* Includes Military Installation */
      "oconusnm"    /* Excludes Military Installation */
    ]
  ];

  if ($conusOconus == "conus") {
    if ($incAll  && $incMil) $fn  = "e_connow";
    if ($incAll  && !$incMil) $fn = "e_conusnm";
    if (!$incAll && $incMil) $fn  = "connow";
    if (!$incAll && !$incMil) $fn = "conusnm";
  } else {
    $fn = ($incMil ? "oconus" : "oconusnm");
  }

  $path = "../data/$fn-20.txt";

  return $path;
}

$app->get('/perdiem/{conus_oconus}/{locality}/{fy_or_pub_date}',
          function(Request $req, Response $resp, array $args){

  $params = $req->getQueryParams();
  $include_mil = $params["include_military"] ?? false;
  $include_all = $params["include_all"] ?? false;
  $path = getDataFilePath($args["conus_oconus"], $include_all, $include_mil);
  $array = parseDataFile($path);
  $loc = $args["locality"];

  $records_in_location = array_filter($array, function($el) use ($loc){
    return strtolower($el[0]) == $loc;
  });

  $resp = $resp->withHeader('Content-type', 'application/json');
  $resp = $resp->withJson($records_in_location);

  return $resp;
});

$app->run();

