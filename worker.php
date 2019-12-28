<?php

use App\CLI\Worker;
use Predis\Client;

$container = require_once __DIR__.'/bootstrap.php';

$predis = $container->get(Client::class);
$worker = new Worker($predis, $container);
$worker->process();
