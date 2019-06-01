<?php

use Dotenv\Dotenv;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use Psr\Container\ContainerInterface;

if (php_sapi_name() != 'cli') {
	ini_set('error_prepend_string', '<pre style="white-space: pre-wrap">');
	ini_set('error_append_string', '</pre>');
	ini_set('html_errors', true);
}

require_once __DIR__ . '/vendor/autoload.php';

Dotenv::create(__DIR__)->load();

define('BR', "<br />\n");
define('TAB', "\t");
define('DEVELOPMENT', true);

ini_set('memory_limit', '1G');
