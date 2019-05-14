<?php

use Dotenv\Dotenv;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use Psr\Container\ContainerInterface;

require_once __DIR__ . '/vendor/autoload.php';

Dotenv::create(__DIR__)->load();

define('BR', "<br />\n");
define('TAB', "\t");
define('DEVELOPMENT', true);

ini_set('memory_limit', '1G');

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

function getPathToThumbsFrom($index)
{
	$storageFolder = ifsetor($_SERVER['argv'][$index]);
	if (!$storageFolder) {
		throw new RuntimeException('php ScanOneFile /path/to/source <project name>');
	}
	if ($storageFolder[0] != '/') {
		$DATA_STORAGE = getenv('DATA_STORAGE');
		if (!$DATA_STORAGE) {
			throw new RuntimeException('Make .env with DATA_STORAGE=' . getcwd());
		}
		$thumbsPath = cap($DATA_STORAGE) . $storageFolder;
	} else {
		$thumbsPath = $storageFolder;	// absolute path provided
	}
	if (!is_dir($thumbsPath)) {
		throw new RuntimeException($thumbsPath.' is not a folder');
	}
//	debug($storageFolder, $thumbsPath);
	return $thumbsPath;
}

$builder = new DI\ContainerBuilder();
$builder->addDefinitions([
	Filesystem::class => function (ContainerInterface $c) {
		return getFlySystem($_SERVER['argv'][2]);
	},
	'FlyThumbs' => function (ContainerInterface $c) {
		return getFlySystem($c->get('PathThumbs'));
	},
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
	ScanExif::class => function (ContainerInterface $c) {
		$thumbsPath = getPathToThumbsFrom(3);
		return new ScanExif($c->get(Filesystem::class), $thumbsPath);
	},
	ScanOneFile::class => function (ContainerInterface $c) {
		$thumbsPath = getPathToThumbsFrom(4);
		$short = $_SERVER['argv'][5];

		// -------------------------------[2]----------------------[3]=file-------------[4]=data/project----[5]=jpg
		return new ScanOneFile($c->get(Filesystem::class), $c->get('ThirdParameter'), $thumbsPath,         $short);
	},
	'ThirdParameter' => function (ContainerInterface $c) {
		return $_SERVER['argv'][3];
	},
	ImgProxy::class => function (ContainerInterface $c) {
		return new ImgProxy($c->get('PathThumbs'));
	}
]);
$container = $builder->build();
