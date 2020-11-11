<?php

use App\CLI\Worker;
use Predis\Client;

ini_set('display_errors', true);
ini_set('error_reporting', E_ALL);

$container = require __DIR__.'/bootstrap.php';
$predis = $container->get(Client::class);
$worker = new Worker($predis, $container);
$worker->process();
