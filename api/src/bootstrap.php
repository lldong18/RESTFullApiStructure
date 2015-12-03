<?php
define('PHP_FILE_CACHE_DIR', '/var/www/cache');
define('CONFIG_FILE_NAME', __DIR__ . '/config/config.yml');

define('APP_CHARSET', 'utf-8');

mb_internal_encoding(APP_CHARSET);

$loader = require __DIR__ . '/../vendor/autoload.php';
