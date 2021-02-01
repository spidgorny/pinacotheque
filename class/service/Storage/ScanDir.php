<?php

namespace App\Service;

use DBInterface;
use DBLayerSQLite;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Source;

/**
 * Class ScanDir - will
 * @package App\Service
 */
class ScanDir
{

	/**
	 * @var \DBLayerSQLite
	 */
	protected $db;

	protected Source $source;

	/**
	 * @var Filesystem
	 */
	protected Filesystem $fileSystem;

	protected string $dir;

	/**
	 * @var callable
	 */
	public $progressCallback;

	public function __construct(DBInterface $db, Source $source)
	{
		$this->db = $db;
		$this->source = $source;
		$this->dir = $source->path;
		$this->fileSystem = new Filesystem(new Local($this->dir));
	}

	public function log(...$msg)
	{
		llog(...$msg);
	}

	public function numFiles()
	{
		$dirs = $this->scandir($this->dir);
		return count($dirs);
	}

	public function __invoke()
	{
		$dirs = $this->scandir($this->dir);
//        $dirs = array_map(static function (array $aFile) {
//            return $aFile;
//        }, $dirs);
		$this->log(count($dirs));
//        print_r(first($dirs));

		$sourceID = $this->source->id;
		foreach ($dirs as $i => $dir) {
			$this->log(count($dirs) - $i, $dir['path']);
//			$query = "INSERT INTO files (source, type, path, timestamp) VALUES ('$sourceID', '${dir['type']}', '${dir['path']}', '${dir['timestamp']}')";
			//echo $query, PHP_EOL;
//			$this->db->perform($query);
			try {
				$ok = \MetaForSQL::insert($this->db, [
					'source' => $sourceID,
					'type' => $dir['type'],
					'path' => $dir['path'],
					'timestamp' => $dir['timestamp'],
				]);
				$this->reportProgress($i, count($dirs), $ok);
			} catch (\Exception $e) {
				// most likely file is already in DB
				$this->reportProgress($i, count($dirs), $e->getMessage());
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
			usort($files, static function ($a, $b) {
				return strcmp($a['path'], $b['path']);
			});
		} catch (RuntimeException $e) {
			// access denied is ignored
		}
		return $files;
	}

	function reportProgress(int $i, int $max, $ok)
	{
		if (!$this->progressCallback) {
			return;
		}
		if (!is_callable($this->progressCallback)) {
			return;
		}
		call_user_func($this->progressCallback, $i, $max, $ok);
	}

}
