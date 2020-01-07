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
	Filesystem::class => static function (ContainerInterface $c) {
		error_log(Filesystem::class);
		return getFlySystem($_SERVER['argv'][2]);
	},
	'FlyThumbs' => static function (ContainerInterface $c) {
		error_log('FlyThumbs');
		return getFlySystem(getenv('DATA_STORAGE'));
	},
	Cameras::class => static function (ContainerInterface $c) {
		return new Cameras($c->get('FlyThumbs'));
	},
	PhotoTimeline::class => static function (ContainerInterface $c) {
		error_log(PhotoTimeline::class);
		return new PhotoTimeline($c->get('FlyThumbs'), $c->get('MetaSet4Thumbs'));
	},
	MonthBrowser::class => static function (ContainerInterface $c) {
		error_log(MonthBrowser::class);
		return MonthBrowser::route($c);
	},
	PhotoGPS::class => static function (ContainerInterface $c) {
		return new PhotoGPS($c->get('PDO'));
	},
	PDO::class => static function (ContainerInterface $c) {
		$db = new PDO('sqlite:' . __DIR__ . '/data/geodb.sqlite');
		return $db;
	},
	ScanExif::class => static function (ContainerInterface $c) {
		$thumbsPath = getPathToThumbsFrom(3);
		return new ScanExif($c->get(Filesystem::class), $thumbsPath);
	},
	ImgProxy::class => static function (ContainerInterface $c) {
		return new ImgProxy(getenv('DATA_STORAGE'));
	},
	'MetaSet4Thumbs' => static function (ContainerInterface $c) {
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
		return new ScanEveryFileFromDB($c->get(DBInterface::class));
	},
	DBInterface::class => static function ($c) {
		if (getenv('mysql')) {
			$m = new DBLayerPDO(getenv('mysql.db'), getenv('mysql'), getenv('mysql.user'), getenv('mysql.password'));
			$m->setQB(new SQLBuilder($m));
			return $m;
		} else {
			return $c->get(DBLayerSQLite::class);
		}
	}
];
