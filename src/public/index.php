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

$app->get('/perdiem/conus', function(Request $req, Response $resp, array $args){
  $file = "../data/connow-20.txt";
  $array = parseDataFile($file);

  $resp = $resp->withHeader('Content-type', 'application/json');
  $resp = $resp->withJson($array);
  return $resp;
});

$app->run();
