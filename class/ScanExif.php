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

	public function __construct(FileSystem $fileSystem)
	{
		$this->fileSystem = $fileSystem;
		/** @var Local $adapter */
		$adapter = $this->fileSystem->getAdapter();
		$this->prefix = $adapter->getPathPrefix();
		$this->prefixMerged = strtr($this->prefix, '/\\:', '___');
	}

	function __invoke()
	{
		echo 'Scanning ', $this->prefix, PHP_EOL;
		$files = $this->fileSystem->listContents('', true);
		echo 'Analyzing...', PHP_EOL;

		$manager = new ImageManager();
		$percent = new Percent(sizeof($files));
//		print_r($files);
		foreach ($files as $file) {
			echo $percent->get(), '%', TAB, $this->prefix, TAB, $file['path'], PHP_EOL;
			$percent->inc();
			$ext = pathinfo($this->prefix.$file['path'], PATHINFO_EXTENSION);
			if (!in_array($ext, $this->imageTypes)) {
				continue;
			}
			$image = $manager
				->make($this->prefix.$file['path']);

			$this->saveMeta($image, $file);
			$this->saveThumbnail($image, $file);
		}
	}

	public function getDestinationFor($suffix)
	{
		$destination = __DIR__.'/../data/thumbs/'.$this->prefixMerged.'/'.$suffix;
		return $destination;
	}

	public function saveMeta(Image $image, array $file)
	{
		$dirName = dirname($file['path']);
		$jsonFile = $this->getDestinationFor($dirName.'/meta.json');
		if (file_exists($jsonFile)) {
			$fileContent = file_get_contents($jsonFile);
			$json = json_decode($fileContent);
		} else {
			$json = (object)[];
		}
		$baseName = basename($file['path']);
		if (isset($json->$baseName)) {
			return;
		}
		$meta = $image->exif();
		$json->$baseName = $meta;
		file_put_contents($jsonFile, json_encode($json), JSON_PRETTY_PRINT);
	}

	public function saveThumbnail(Image $image, array $file)
	{
		$destination = $this->getDestinationFor($file['path']);
		if (file_exists($destination)) {
			return;
		}
		@mkdir(dirname($destination), 0777, true);
		$image->resize(256, null, function (Constraint $constraint) {
			$constraint->aspectRatio();
		});
		$image->save($destination);
	}

}
