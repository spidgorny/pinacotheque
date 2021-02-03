<?php

namespace ScanDir;

class ScanDirRecursive
{

	protected string $dir;

	public function __construct(string $dir)
	{
		$this->dir = $dir;
	}

	public function scandir()
	{
		$files = $this->getDirContents($this->dir);

		$files = array_map(function ($el) {
			$without = str_replace($this->dir, '', $el);
			$without = ltrim($without, '\\');
			$without = ltrim($without, '/');
//			llog('replace', $this->dir, $el, $without);
			$file = \File::fromLocal($without, $this->dir);
			return $file;
		}, $files);

		return $files;
	}

	function getDirContents($dir, &$results = array()) {
		$files = scandir($dir);

		foreach ($files as $key => $value) {
			$path = ($dir . DIRECTORY_SEPARATOR . $value);
			if (!is_dir($path)) {
				$results[] = $path;
			} elseif ($value !== "." && $value !== ".." && $value !== '@eaDir') {
				$this->getDirContents($path, $results);
				$results[] = $path;
			}
		}

		return $results;
	}
}
