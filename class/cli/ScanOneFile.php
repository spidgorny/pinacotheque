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

	protected $prefix;

	/** @var DBInterface */
	protected $db;

	/**
	 * ScanOneFile constructor.
	 *
	 * @param Filesystem $fileSystem
	 * @param DBInterface $db
	 */
	public function __construct(Filesystem $fileSystem, DBInterface $db)
	{
		parent::__construct();
//		debug($_SERVER['argv']);
		$this->fileSystem = $fileSystem;
		/** @var Local $adapter */
		$adapter = $this->fileSystem->getAdapter();
		$this->prefix = realpath($adapter->getPathPrefix());
		$this->log('Prefix', $this->prefix);

		$this->db = $db;
	}

	public function __invoke()
	{
		$file = $_SERVER['argv'][3];
		$this->log('File', $file);

		if (!is_file($file)) {
			throw new InvalidArgumentException($file . ' not found');
		}

		$meta = new Meta([
			'_path_' => $file,
		]);
		$meta->sourcePath = basename($this->prefix);	// .../data/[thumbRoot]/
		$this->scan($meta);
	}

	public function scan(IMetaData $file)
	{
		$this->log($file);
		$this->log('Source', $file->getSource());
//		$this->log();
		$destination = $file->getDestination();
		$this->log('Destination', $destination);
		$is = new ImageScanner($file, $this->db);
		$is();
	}

}
