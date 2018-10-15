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

function __($a)
{
	return $a;
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
	'FlyThumbs' => getFlySystem(__DIR__.'/data/thumbs'),
	Cameras::class => function (ContainerInterface $c) {
		return new Cameras($c->get('FlyThumbs'));
	},
	PhotoTimeline::class => function (ContainerInterface $c) {
		return new PhotoTimeline($c->get('FlyThumbs'));
	},
	MonthBrowser::class => function (ContainerInterface $c) {
		return MonthBrowser::route($c);
	},
	PhotoGPS::class => function (ContainerInterface $c) {
		return new PhotoGPS($c->get('PDO'));
	},
	PDO::class => function (ContainerInterface $c) {
		$db = new PDO('sqlite:'.__DIR__.'/data/geodb.sqlite');
		return $db;
	},
]);
$container = $builder->build();

if (php_sapi_name() == 'cli') {
	$c = $_SERVER['argv'][1];
} else {
	$c = ifsetor($_SERVER['PATH_INFO'], PhotoTimeline::class);
	$c = first(trimExplode('/', $c));
}
$o = $container->get($c);
$content = $o();
if (is_array($content)) {
	$content = implode(PHP_EOL, $content);
}
echo $content;
