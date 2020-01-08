<?php

namespace App\Service;

use DBInterface;
use DBLayerSQLite;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Source;

class ScanDir
{

	/**
	 * @var \DBLayerSQLite
	 */
	protected $db;


	protected $source;

	/**
	 * @var Filesystem
	 */
	protected $fileSystem;

	protected $dir;

	public function __construct(DBInterface $db, Source $source)
	{
		$this->db = $db;
		$this->source = $source;
		$this->dir = $source->path;
		$this->fileSystem = new Filesystem(new Local($this->dir));
	}

	public function log(...$msg)
	{
		echo implode(' ', $msg), PHP_EOL;
	}

	public function numFiles()
	{
		$dirs = $this->scandir($this->dir);
		return sizeof($dirs);
	}

	public function __invoke()
	{
		$dirs = $this->scandir($this->dir);
//        $dirs = array_map(static function (array $aFile) {
//            return $aFile;
//        }, $dirs);
		echo sizeof($dirs), PHP_EOL;
//        print_r(first($dirs));

		$sourceID = $this->source->id;
		foreach ($dirs as $i => $dir) {
			echo count($dirs) - $i, TAB, $dir['path'], PHP_EOL;
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
			} catch (\Exception $e) {
				// most likely file is already in DB
			}
		}
	}

	public function scandir($dir)
	{
		$files = [];
//		$this->log('Scanning', $dir);
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
