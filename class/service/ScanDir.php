<?php

namespace App\Service;

use DBLayerSQLite;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Source;

class ScanDir
{

	protected $dir;

	/**
	 * @var Filesystem
	 */
	protected $fileSystem;

	/**
	 * @var \DBLayerSQLite
	 */
	protected $db;

	public function __construct(DBLayerSQLite $db, $dir)
	{
		$this->dir = $dir;
		$this->fileSystem = new Filesystem(new Local($this->dir));
		$this->db = $db;
	}

	public function log(...$msg)
	{
		echo implode(' ', $msg), PHP_EOL;
	}

	public function __invoke()
	{
		$source = \Source::findOne($this->db, [
			'path' => $this->dir,
		]);
		$dirs = $this->scandir($this->dir);
//        $dirs = array_map(static function (array $aFile) {
//            return $aFile;
//        }, $dirs);
		echo sizeof($dirs), PHP_EOL;
//        print_r(first($dirs));

		$sourceID = $source->id;
		foreach ($dirs as $dir) {
			echo $dir['path'], PHP_EOL;
//			$query = "INSERT INTO files (source, type, path, timestamp) VALUES ('$sourceID', '${dir['type']}', '${dir['path']}', '${dir['timestamp']}')";
			//echo $query, PHP_EOL;
//			$this->db->perform($query);
			try {
				\MetaForSQL::insert($this->db, [
					'source' => $sourceID,
					'type' => $dir['type'],
					'path' => $dir['path'],
					'timestamp' => $dir['timestamp'],
				]);
			} catch (\DatabaseException $e) {
				// most likely file is already in DB
			}
		}
	}

	public function scandir($dir)
	{
		$files = [];
		$this->log('Scanning', $dir);
		$dirWithoutPrefix = str_replace($this->dir, '', $dir);
		try {
			$files = $this->fileSystem->listContents($dirWithoutPrefix, true);
			usort($files, function ($a, $b) {
				return strcmp($a['path'], $b['path']);
			});
		} catch (RuntimeException $e) {
		}
		return $files;
	}

}