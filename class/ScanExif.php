<?php

use Graze\ParallelProcess\Display\Table;
use Graze\ParallelProcess\PriorityPool;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Symfony\Component\Console\Output\ConsoleOutput;
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
		$this->log($php);

		$pool = new PriorityPool();
		$percent = new Percent(sizeof($files));
//		print_r($files);
		foreach ($files as $file) {
			if (basename($file['path'])[0] == '.') {
				continue;
			}
			$percent->inc();
			$pathFile = $this->prefix . '/' . $file['path'];
			$ext = pathinfo($pathFile, PATHINFO_EXTENSION);
			if (!in_array($ext, $this->imageTypes)) {
				continue;
			}

			// ignore files which have thumbnails
			$scan = new ScanOneFile($this->fileSystem, $pathFile, $file['path']);
			$thumbFile = $scan->getDestinationFor($file['path']);
			if (!is_file($thumbFile)) {
				$this->log($percent->get() . ' %', $this->prefix, $file['path']);
//	    		$scan();
				$p = new Process([$php, 'index.php', ScanOneFile::class, $this->prefix, $pathFile, $file['path']]);
				$pool->add($p);
			}
		}
		$this->log('Processing...');
		$pool->setMaxSimultaneous(4);

		$existing = new ConsoleOutput(ConsoleOutput::VERBOSITY_VERY_VERBOSE);
		$table = new Table($existing, $pool);
		$table->run();

		$this->log('Done');
	}

}
