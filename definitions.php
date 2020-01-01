<?php

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Predis\Client;
use Psr\Container\ContainerInterface;

require_once __DIR__ . '/autoload.php';

if (!function_exists('getFlySystem')) {
	function getFlySystem($root = __DIR__ . '/data')
	{
		$adapter = new Local($root);
		$filesystem = new Filesystem($adapter);
		return $filesystem;
	}
}

//error_log(__FILE__);

return [
	Filesystem::class => function (ContainerInterface $c) {
		error_log(Filesystem::class);
		return getFlySystem($_SERVER['argv'][2]);
	},
	'FlyThumbs' => function (ContainerInterface $c) {
		error_log('FlyThumbs');
		return getFlySystem(getenv('DATA_STORAGE'));
	},
	Cameras::class => function (ContainerInterface $c) {
		return new Cameras($c->get('FlyThumbs'));
	},
	PhotoTimeline::class => function (ContainerInterface $c) {
		error_log(PhotoTimeline::class);
		return new PhotoTimeline($c->get('FlyThumbs'), $c->get('MetaSet4Thumbs'));
	},
	MonthBrowser::class => function (ContainerInterface $c) {
		error_log(MonthBrowser::class);
		return MonthBrowser::route($c);
	},
	PhotoGPS::class => function (ContainerInterface $c) {
		return new PhotoGPS($c->get('PDO'));
	},
	PDO::class => function (ContainerInterface $c) {
		$db = new PDO('sqlite:' . __DIR__ . '/data/geodb.sqlite');
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
		return new ScanOneFile($c->get(Filesystem::class), $c->get('ThirdParameter'), $thumbsPath, $short);
	},
	'ThirdParameter' => function (ContainerInterface $c) {
		return $_SERVER['argv'][3];
	},
	ImgProxy::class => function (ContainerInterface $c) {
		return new ImgProxy(getenv('DATA_STORAGE'));
	},
	'MetaSet4Thumbs' => function (ContainerInterface $c) {
		if (ifsetor($_SESSION['MetaSet4Thumbs'])) {
			return $_SESSION['MetaSet4Thumbs'];
		}
		error_log(MetaSet::class);
		$filesystem = $c->get('FlyThumbs');
		/** @var \League\Flysystem\Adapter\Local $adapter */
		$adapter = $filesystem->getAdapter();
		$prefix = new Path(
			$adapter->getPathPrefix()
		);
		error_log('prefix=' . $prefix);
//		var_dump(['prefix' => $prefix]);1
		$ms = new MetaSet(getFlySystem($prefix));
		$_SESSION['MetaSet4Thumbs'] = $ms;
		return $ms;
	},
	DBLayerSQLite::class => static function ($c) {
		$db = new \DBLayerSQLite(__DIR__ . '/data/database.sqlite');
		$qb = new SQLBuilder($db);
		$db->setQB($qb);
		return $db;
	},
	Client::class => static function ($c) {
		$predis = new Client('tcp://127.0.0.1', array(
			'prefix' => 'bernard:',
		));
		return $predis;
	},
	ScanEveryFileFromDB::class => static function ($c) {
		return new ScanEveryFileFromDB($c->get(DBLayerSQLite::class));
	},
];
