<?php

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;

require_once __DIR__ . '/vendor/autoload.php';

define('TAB', "\t");

function getContext()
{
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
	return $context;
}

function getFlySystem()
{
	$adapter = new Local(__DIR__.'/data');
	$filesystem = new Filesystem($adapter);
	return $filesystem;
}

$c = $_SERVER['argv'][1];
$o = new $c(getFlySystem());
$o();
