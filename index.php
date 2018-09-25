<?php

require_once __DIR__ . '/vendor/autoload.php';

$options = [
	'http' => [
		'proxy' => getenv('https_proxy'),
		'request_fulluri' => true,
	],
	'https' => [
		'proxy' => getenv('https_proxy'),
		'request_fulluri' => true,
	],
];
print_r($options);
$context = stream_context_create($options);

$c = $_SERVER['argv'][1];
$o = new $c($context);
$o();
