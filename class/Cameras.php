<?php

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

class Cameras
{

	protected $fileSystem;

	/**
	 * @var string
	 */
	protected $prefix;

	/**
	 * Cameras constructor.
	 *
	 * @param Filesystem $fileSystem
	 * @Inject Filesystem4data
	 */
	public function __construct(FileSystem $fileSystem)
	{
		$this->fileSystem = $fileSystem;
		/** @var Local $adapter */
		$adapter = $this->fileSystem->getAdapter();
		$this->prefix = __DIR__ . '/../data/thumbs';
		$adapter->setPathPrefix($this->prefix);
	}

	public function __invoke()
	{
		echo 'Scanning...', PHP_EOL;
		$files = $this->fileSystem->listContents('', true);
		echo 'Analyzing...', PHP_EOL;
		$models = [];

		foreach ($files as $file) {
			$baseName = basename($file['path']);
			if ($baseName != 'meta.json') {
				continue;
			}

			echo $this->prefix, TAB, $file['path'], PHP_EOL;
			$images = $this->readMeta($file);
			foreach ($images as $meta) {
				$models[$meta->Model] = ifsetor($models[$meta->Model], 0);
				$models[$meta->Model]++;
			}
		}
		print_r($models);
	}

	public function readMeta(array $file)
	{
		$fileContent = file_get_contents($this->prefix.'/'.$file['path']);
		$json = json_decode($fileContent);
		return $json;
	}

}
