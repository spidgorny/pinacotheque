<?php

namespace ScanDir;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

class FlySystem
{

	protected string $dir;

	/**
	 * @var Filesystem
	 */
	protected Filesystem $fileSystem;

	public function __construct(string $dir)
	{
		$this->dir = $dir;
		$this->fileSystem = new Filesystem(new Local($dir));
	}

	// TODO: this function does not scan symlinks
	// replace with SPL version
	public function scandirRaw()
	{
		$files = [];
//		$this->log('Scanning', $dir);
		try {
			$files = $this->fileSystem->listContents('', true);
			usort($files, static function ($a, $b) {
				return strcmp($a['path'], $b['path']);
			});
		} catch (RuntimeException $e) {
			// access denied is ignored
		}

//		$files = array_filter($files, static function ($path) {
//			if (str_contains($path, '/@eaDir/')) {
//				return false;
//			}
//			return true;
//		});
		return $files;
	}

	public function scandir()
	{
		$files = $this->scandirRaw();
		$files = array_map(function (array $el) {
			return \File::fromFly($this->fileSystem, $el);
		}, $files);

		return $files;
	}

}
