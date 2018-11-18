<?php

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class ScanExif extends BaseController
{

	/**
	 * @var Filesystem
	 */
	protected $fileSystem;

	protected $imageTypes = [
		'jpg',
		'jpeg',
		'gif',
		'bmp',
		'png',
		'tiff',
		'tif',
	];

	/**
	 * @var string
	 */
	protected $prefix;

	public function __construct(Filesystem $fileSystem)
	{
		$this->fileSystem = $fileSystem;
		/** @var Local $adapter */
		$adapter = $this->fileSystem->getAdapter();
		$this->prefix = realpath($adapter->getPathPrefix());
	}

	function __invoke()
	{
		$this->log('Scanning', $this->prefix);
		$files = $this->fileSystem->listContents('', true);
		$this->log('Analyzing...');

		$phpBinaryFinder = new PhpExecutableFinder();
		$php = $phpBinaryFinder->find();

		$percent = new Percent(sizeof($files));
//		print_r($files);
		foreach ($files as $file) {
			if (basename($file['path'])[0] == '.') {
				continue;
			}
			$this->log($percent->get(), '%', $this->prefix, $file['path']);
			$percent->inc();
			$pathFile = $this->prefix . '/' . $file['path'];
			$ext = pathinfo($pathFile, PATHINFO_EXTENSION);
			if (!in_array($ext, $this->imageTypes)) {
				continue;
			}

			$scan = new ScanOneFile($this->fileSystem, $pathFile, $file['path']);
			$scan();
//			$p = new Process([$php, 'ScanOneFile', $pathFile]);
//			$p->start();
//			$p->wait();
		}
		$this->log('Done');
	}

}
