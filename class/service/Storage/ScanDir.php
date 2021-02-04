<?php

namespace App\Service;

use DBInterface;
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
		$files = $this->scanner->scandir();
		$this->log(count($files));

		$this->source->update([
			'mtime' => new \SQLNow(),
			'files' => count($files),
		]);

		$inserted = 0;
		$sourceID = $this->source->id;
		/**
		 * @var int $i
		 * @var \File $file
		 */
		foreach ($files as $i => $file) {
			$this->log(count($files) - $i, $file->getName());
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
				$this->reportProgress($file->getName(), $i, count($files), 'ok');
				$inserted++;
			} catch (\PDOException $e) {
				// most likely file is already in DB
				$this->reportProgress($file->getName(), $i, count($files), $e->getMessage());
			}
		}

		$this->source->update([
			'mtime' => new \SQLNow(),
			'files' => count($files),
			'inserted' => $inserted,
		]);

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
