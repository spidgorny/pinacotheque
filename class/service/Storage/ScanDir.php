<?php

namespace App\Service;

use DBInterface;
use DBLayerSQLite;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use ScanDir\FlySystem;
use ScanDir\ScanDirRecursive;
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

	protected string $dir;

	/**
	 * @var callable
	 */
	public $progressCallback;

//	protected FlySystem $scanner;
	protected ScanDirRecursive $scanner;

	public function __construct(DBInterface $db, Source $source)
	{
		$this->db = $db;
		$this->source = $source;
		$this->dir = $source->path;
//		$this->scanner = new FlySystem($this->dir);
		$this->scanner = new ScanDirRecursive($this->dir);
	}

	public function log(...$msg)
	{
		llog(...$msg);
	}

	public function numFiles()
	{
		$dirs = $this->scanner->scandir();
		return count($dirs);
	}

	public function __invoke()
	{
		$dirs = $this->scanner->scandir();
		$this->log(count($dirs));

		$inserted = 0;
		$sourceID = $this->source->id;
		/**g
		 * @var int $i
		 * @var \File $file
		 */
		foreach ($dirs as $i => $file) {
			$this->log(count($dirs) - $i, $file->getName());
			try {
				/** @var \PDOStatement $ok */
				$insert = [
					'source' => $sourceID,
					'type' => $file->getType(),
					'path' => $file->getName(),
					'timestamp' => $file->getMTime(),
				];
//				llog($insert);
				$ok = \MetaForSQL::insert($this->db, $insert);
				$this->reportProgress($file->getName(), $i, count($dirs), 'ok');
				$inserted++;
			} catch (\PDOException $e) {
				// most likely file is already in DB
				$this->reportProgress($file->getName(), $i, count($dirs), $e->getMessage());
			}
		}
		return $inserted;
	}

	function reportProgress(string $file, int $i, int $max, $ok)
	{
		if (!$this->progressCallback) {
			return;
		}
		if (!is_callable($this->progressCallback)) {
			return;
		}
		call_user_func($this->progressCallback, $file, $i, $max, $ok);
	}

}
