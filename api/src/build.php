<?php
$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->add('Controllers', __DIR__ . '/Api');
$buildDir = __DIR__ . '/../out/build';

// Read api data
$options = array(
  'controllersDir' => __DIR__ . '/Api/Controllers',
  'namespace' => 'Controllers',

  'export' => array(

    'Silex' => array(
      'exportDir' => $buildDir . '/controllers',
      'newNamespace' => 'Api\ControllerCollection',
      'rootController' => 'HomeController',
    ),

    'JsonApiDoc' => array(
      'exportDir' => $buildDir . '/docs',
      'filename' => 'api.json.js',
      'amd' => true,
      'rootController' => 'HomeController',
    )

  )
);

$creator = new ApiCreator\ApiCreator($options);

$api = $creator->generate();

echo "\nDone exporting!\n\n";
