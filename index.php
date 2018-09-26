<?php

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use Psr\Container\ContainerInterface;

require_once __DIR__ . '/vendor/autoload.php';

define('BR', "<br />\n");
define('TAB', "\t");
define('DEVELOPMENT', true);

ini_set ('memory_limit', '1G');

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

function getFlySystem($root = __DIR__.'/data')
{
	$adapter = new Local($root);
	$filesystem = new Filesystem($adapter);
	return $filesystem;
}

$builder = new DI\ContainerBuilder();
$builder->addDefinitions([
	Filesystem::class => function (ContainerInterface $c) {
		return getFlySystem($_SERVER['argv'][2]);
	},
	Cameras::class => function (ContainerInterface $c) {
		return new Cameras(getFlySystem());
	},
]);
$container = $builder->build();

if (php_sapi_name() == 'cli') {
	$c = $_SERVER['argv'][1];
} else {
	$c = ifsetor($_SERVER['PATH_INFO'], PhotoTimeline::class);
}
$o = $container->get($c);
echo $o();
