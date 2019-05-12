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

	/**
	 * @var string
	 */
	protected $prefixMerged;

	protected $shortened;

	/**
	 * @var string
	 */
	protected $thumbsPath;

	/**
	 * ScanOneFile constructor.
	 *
	 * @param Filesystem $fileSystem
	 * @param $file
	 * @param $shortened
	 */
	public function __construct(Filesystem $fileSystem, $file, $shortened, $thumbsPath)
	{
		$this->fileSystem = $fileSystem;
		/** @var Local $adapter */
		$adapter = $this->fileSystem->getAdapter();
		$this->prefix = realpath($adapter->getPathPrefix());
		$this->prefixMerged = strtr($this->prefix, '/\\:', '___');

		$this->file = $file;
		if (!is_file($this->file)) {
			throw new InvalidArgumentException($this->file.' not found');
		}

		$this->shortened = $shortened;
		$this->thumbsPath = $thumbsPath;
	}

	public function __invoke()
	{
		$this->log($this->file);
//		$this->log('Destination: ', $this->getDestinationFor(''));
		$manager = new ImageManager();
		$imagePromise = function () use ($manager) {
			static $path;
			static $image;
			if ($path == $this->file && $image) {
				return $image;
			}

			$path = $this->file;
			$image = $manager->make($path);
			return $image;
		};

		$this->saveMeta($imagePromise, $this->shortened);
		$this->saveThumbnail($imagePromise, $this->shortened);
	}

	public function getDestinationFor($suffix)
	{
		$destination = cap($this->thumbsPath) .$suffix;
		@mkdir(dirname($destination), 0777, true);
		$real = realpath($destination);	// after mkdir()
		if ($real) {
			$destination = $real;
		}
		return $destination;
	}

	public function saveMeta(callable $imagePromise, $file)
	{
		$dirName = dirname($file);
		$jsonFile = $this->getDestinationFor($dirName.'/meta.json');
		echo $jsonFile, PHP_EOL;
		$json = $this->getCachedJSONFrom($jsonFile);
		$baseName = basename($file);
		if (isset($json->$baseName)) {
			return;
		}
		try {
			/** @var Image $image */
			$image = $imagePromise();
			$meta = $image->exif();
			echo 'meta keys: ', sizeof($meta), PHP_EOL;
			$json->$baseName = $meta;
			file_put_contents($jsonFile, json_encode($json, JSON_PRETTY_PRINT));
		} catch (Intervention\Image\Exception\NotReadableException $e) {
			echo '** Error: '.$e->getMessage(), PHP_EOL;
		}
	}

	/**
	 * @param callable $imagePromise
	 * @param string $file
	 */
	public function saveThumbnail(callable $imagePromise, $file)
	{
		$destination = $this->getDestinationFor($file);
		if (file_exists($destination)) {
			return;
		}

		try {
			/** @var Image $image */
			$image = $imagePromise();
			$image->resize(256, null, function (Constraint $constraint) {
				$constraint->aspectRatio();
			});

			$image->save($destination);
		} catch (Intervention\Image\Exception\NotReadableException $e) {
			echo '** Error: '.$e->getMessage(), PHP_EOL;
		}
	}

	public function getCachedJSONFrom($jsonFile)
	{
		static $jsonPath;
		static $jsonData = [];

		if ($jsonFile == $jsonPath && $jsonData) {
			return $jsonData;
		}

		if (file_exists($jsonFile)) {
			$fileContent = file_get_contents($jsonFile);
			$jsonData = json_decode($fileContent);
		} else {
			$jsonData = [];
		}

		$jsonPath = $jsonFile;
		return (object)$jsonData;
	}

}
