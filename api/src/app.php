<?php
require_once __DIR__ . '/bootstrap.php';

$configFileReader = new AppConfigFileReader($appRoot = __DIR__ . '/../', $debug);
$config = $configFileReader->get(CONFIG_FILE_NAME);

$app = new Api\ApiApplication($config);

return $app;
