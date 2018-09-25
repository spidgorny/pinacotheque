<?php

use Intervention\Image\Constraint;
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
		'tif'
	];

	public function __construct(FileSystem $fileSystem)
	{
		$this->fileSystem = $fileSystem;
	}

	function __invoke()
	{
		$manager = new ImageManager();
		$files = $this->fileSystem->listContents('', true);
//		print_r($files);
		foreach ($files as $file) {
			/** @var Local $adapter */
			$adapter = $this->fileSystem->getAdapter();
			$prefix = $adapter->getPathPrefix();
			$ext = pathinfo($prefix.$file['path'], PATHINFO_EXTENSION);
			if (!in_array($ext, $this->imageTypes)) {
				continue;
			}
			$image = $manager->make($prefix.$file['path'])->resize(256, null, function (Constraint $constraint) {
				$constraint->aspectRatio();
			});

			$prefixMerged = strtr($prefix, '/\\:', '___');
			$destination = __DIR__.'/../data/thumbs/'.$prefixMerged.'/'.$file['path'];
			if (file_exists($destination)) {
				//continue;
			}
			echo $prefix, TAB, $file['path'], PHP_EOL;
			@mkdir(dirname($destination), 0777, true);
			$image->save($destination);
		}
	}

}
