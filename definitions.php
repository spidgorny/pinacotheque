<?php

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Psr\Container\ContainerInterface;

require_once __DIR__.'/autoload.php';

function getFlySystem($root = __DIR__.'/data')
{
	$adapter = new Local($root);
	$filesystem = new Filesystem($adapter);
	return $filesystem;
}


return [
	Filesystem::class => function (ContainerInterface $c) {
		return getFlySystem($_SERVER['argv'][2]);
	},
	'FlyThumbs' => function (ContainerInterface $c) {
		return getFlySystem(getenv('DATA_STORAGE'));
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
		return new ImgProxy(getenv('DATA_STORAGE'));
	}
];