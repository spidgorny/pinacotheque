<?php

use Intervention\Image\Constraint;
use Intervention\Image\Image;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Intervention\Image\ImageManager;

class ScanExif {

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
	protected $prefixMerged;

	public function __construct(Filesystem $fileSystem)
	{
		$this->fileSystem = $fileSystem;
		/** @var Local $adapter */
		$adapter = $this->fileSystem->getAdapter();
		$this->prefix = realpath($adapter->getPathPrefix());
		$this->prefixMerged = strtr($this->prefix, '/\\:', '___');
	}

	function __invoke()
	{
		echo 'Scanning ', $this->prefix, PHP_EOL;
		$files = $this->fileSystem->listContents('', true);
		echo 'Destination: ', $this->getDestinationFor(''), PHP_EOL;
		echo 'Analyzing...', PHP_EOL;

		$manager = new ImageManager();
		$percent = new Percent(sizeof($files));
//		print_r($files);
		foreach ($files as $file) {
			if (basename($file['path'])[0] == '.') {
				continue;
			}
			echo $percent->get(), '%', TAB, $this->prefix, TAB, $file['path'], PHP_EOL;
			$percent->inc();
			$ext = pathinfo($this->prefix.'/'.$file['path'], PATHINFO_EXTENSION);
			if (!in_array($ext, $this->imageTypes)) {
				continue;
			}

			$imagePromise = function () use ($manager, $file) {
				static $path;
				static $image;
				if ($path == $this->prefix . '/' . $file['path'] && $image) {
					return $image;
				}

				$path = $this->prefix . '/' . $file['path'];
				$image = $manager->make($path);
				return $image;
			};

			$this->saveMeta($imagePromise, $file);
			$this->saveThumbnail($imagePromise, $file);
		}
	}

	public function getDestinationFor($suffix)
	{
		$destination = __DIR__.'/../data/thumbs/'.$this->prefixMerged.'/'.$suffix;
		@mkdir(dirname($destination), 0777, true);
		$real = realpath($destination);	// after mkdir()
		if ($real) {
			$destination = $real;
		}
		return $destination;
	}

	public function saveMeta(callable $imagePromise, array $file)
	{
		$dirName = dirname($file['path']);
		$jsonFile = $this->getDestinationFor($dirName.'/meta.json');
		$json = $this->getCachedJSONFrom($jsonFile);
		$baseName = basename($file['path']);
		if (isset($json->$baseName)) {
			return;
		}
		try {
			/** @var Image $image */
			$image = $imagePromise();
			$meta = $image->exif();
			$json->$baseName = $meta;
			file_put_contents($jsonFile, json_encode($json, JSON_PRETTY_PRINT));
		} catch (Intervention\Image\Exception\NotReadableException $e) {
			echo '** Error: '.$e->getMessage(), PHP_EOL;
		}
	}

	public function saveThumbnail(callable $imagePromise, array $file)
	{
		$destination = $this->getDestinationFor($file['path']);
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

	private function getCachedJSONFrom($jsonFile)
	{
		static $jsonPath;
		static $jsonData;

		if ($jsonFile == $jsonPath && $jsonData) {
			return $jsonData;
		}

		if (file_exists($jsonFile)) {
			$fileContent = file_get_contents($jsonFile);
			$jsonData = json_decode($fileContent);
		} else {
			$jsonData = (object)[];
		}

		$jsonPath = $jsonFile;
		return $jsonData;
	}

}
