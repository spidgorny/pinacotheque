<?php

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

class Cameras
{

	protected $fileSystem;

	public function __construct(FileSystem $fileSystem)
	{
		$this->fileSystem = $fileSystem;
		/** @var Local $adapter */
		$adapter = $this->fileSystem->getAdapter();
		$adapter->setPathPrefix(__DIR__.'/../data/thumbs');
	}

	public function __invoke()
	{
		echo 'Scanning...', PHP_EOL;
		$files = $this->fileSystem->listContents('', true);
		echo 'Analyzing...', PHP_EOL;

		foreach ($files as $file) {
			$baseName = basename($file['path']);
			if ($baseName != 'meta.json') {
				continue;
			}

			echo $files['path'];
			$images = $this->readMeta($file);
		}
	}

	public function readMeta(array $file)
	{
		$fileContent = file_get_contents($file['path']);

	}

}
