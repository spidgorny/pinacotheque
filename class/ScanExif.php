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
		'tif',
	];

	public function __construct(FileSystem $fileSystem)
	{
		$this->fileSystem = $fileSystem;
	}

	function __invoke()
	{
		/** @var Local $adapter */
		$adapter = $this->fileSystem->getAdapter();
		$prefix = $adapter->getPathPrefix();
		echo 'Scanning ', $prefix, PHP_EOL;

		$manager = new ImageManager();
		$files = $this->fileSystem->listContents('', true);
		echo 'Analyzing...', PHP_EOL;

		$percent = new Percent(sizeof($files));
//		print_r($files);
		foreach ($files as $file) {
			echo $percent->get(), '%', TAB, $prefix, TAB, $file['path'], PHP_EOL;
			$percent->inc();
			$ext = pathinfo($prefix.$file['path'], PATHINFO_EXTENSION);
			if (!in_array($ext, $this->imageTypes)) {
				continue;
			}
			$image = $manager
				->make($prefix.$file['path'])
				->resize(256, null, function (Constraint $constraint) {
				$constraint->aspectRatio();
			});

			$prefixMerged = strtr($prefix, '/\\:', '___');
			$destination = __DIR__.'/../data/thumbs/'.$prefixMerged.'/'.$file['path'];
			if (file_exists($destination)) {
				continue;
			}
			@mkdir(dirname($destination), 0777, true);
			$image->save($destination);
		}
	}

}
