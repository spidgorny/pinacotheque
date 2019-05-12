<?php

use Graze\ParallelProcess\Display\Lines;
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

	/**
	 * @var string
	 */
	protected $thumbsPath;

	public function __construct(Filesystem $fileSystem, $thumbsPath)
	{
		$this->fileSystem = $fileSystem;
		/** @var Local $adapter */
		$adapter = $this->fileSystem->getAdapter();
		$this->prefix = realpath($adapter->getPathPrefix());
		$this->thumbsPath = $thumbsPath;
	}

	function __invoke()
	{
		$iterator = new RecursiveDirectoryIterator($this->prefix);
		/** @var SplFileInfo $dir */
		foreach ($iterator as $dir) {
			if ($dir->getFilename()[0] != '.') {
				echo '>>> ', $dir, PHP_EOL;
				$files = $this->getFiles($dir);
				echo 'files: ', sizeof($files), PHP_EOL;

				$pool = $this->analyze($files);
				$this->process($pool);
			}
		}
	}

	public function getFiles($dir)
	{
		$cache = new FileCache();
		$files = $cache->get($dir, function () use ($dir) {
			$this->log('Scanning', $dir);
			$dirWithoutPrefix = str_replace($this->prefix, '', $dir);
			$files = $this->fileSystem->listContents($dirWithoutPrefix, false);
			return $files;
		});
		return $files;
	}

	public function analyze(array $files)
	{
		$this->log('Analyzing... '.sizeof($files));

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
			$scan = new ScanOneFile($this->fileSystem, $pathFile, $file['path'], $this->thumbsPath);
			$thumbFile = $scan->getDestinationFor($file['path']);
			if (!is_file($thumbFile)) {
				$this->log($percent->get() . ' %', $this->prefix, $file['path']);
//	    		$scan();
				$p = new Process([$php, 'index.php', ScanOneFile::class, $this->prefix, $pathFile, $file['path']]);
				$pool->add($p);
			}
		}
		return $pool;
	}

	public function process(PriorityPool $pool)
	{
		$this->log('Processing...');
		$pool->setMaxSimultaneous(4);
//		$pool->run();

		$output = new ConsoleOutput(ConsoleOutput::VERBOSITY_VERY_VERBOSE);
		$lines = new Lines($output, $pool);
		$lines->run();
//		$table = new Table($output, $pool);
//		$table->run();

		$this->log('Done');
	}

}
