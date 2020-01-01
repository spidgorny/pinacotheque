<?php

use DI\Annotation\Inject;
use Intervention\Image\Constraint;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

class ScanOneFile extends BaseController
{

	/**
	 * @var Filesystem
	 */
	protected $fileSystem;

	protected $file;

	protected $prefix;

	protected $shortened;

	/**
	 * @var string
	 */
	protected $thumbsPath;

	/**
	 * ScanOneFile constructor.
	 *
	 * @param Filesystem $fileSystem
	 * @param string $file
	 * @param string $thumbsPath
	 * @param string $shortened
	 */
	public function __construct(Filesystem $fileSystem, $file, $thumbsPath, $shortened)
	{
//		debug($_SERVER['argv']);
		$this->fileSystem = $fileSystem;
		/** @var Local $adapter */
		$adapter = $this->fileSystem->getAdapter();
		$this->prefix = realpath($adapter->getPathPrefix());

		$this->file = $file;
		if (!is_file($this->file)) {
			throw new InvalidArgumentException($this->file . ' not found');
		}

		$this->thumbsPath = $thumbsPath;
		$this->shortened = $shortened;
	}

	public function __invoke()
	{
		$this->log($this->file);
//		$this->log('Prefix', $this->prefix);
//		$this->log('Thumbs path', $this->thumbsPath);
//		$this->log('Shortened', $this->shortened);
//		$this->log('Destination', $this->getDestinationFor($this->shortened));
//		$this->log('Destination: ', $this->getDestinationFor(''));
//		$this->log('Done');
		$is = new ImageScanner($this->file, $this->thumbsPath);
		$is();
	}

}
