<?php

use Intervention\Image\ImageManager;

class ImageScanner
{

	protected $source;

	public $file;

	public $thumbsPath;	// root for thumbs

	public function __construct(Source $source, $file, $thumbsPath)
	{
		$this->source = $source;
		$this->file = $file;
		$this->thumbsPath = $thumbsPath;
	}

	public function __invoke()
	{
		try {
			$image = $this->readImage();
			$ip = new ImageParser($image);

			$metaFile = new MetaFile($this->thumbsPath, $this->file);
//			debug($metaFile);
			if (!$metaFile->has($this->file)) {
				$meta = $ip->getMeta();
//				debug($meta);
//				echo 'Meta has ', sizeof($meta), PHP_EOL;
				$metaFile->set(basename($this->file), $meta);
				//			$this->saveMetaToDB($meta);
			}

			$destination = $metaFile->getDestinationFor($this->file);
			if (!file_exists($destination)) {
				$ip->saveThumbnailTo($destination);
			}

		} catch (Intervention\Image\Exception\NotReadableException $e) {
			echo '** Error: ' . $e->getMessage(), PHP_EOL;
		}
	}

	public function readImage()
	{
		$manager = new ImageManager();
		$imagePromise = function () use ($manager) {
			static $path;
			static $image;
			if ($path == $this->file && $image) {
				return $image;
			}

			$path = path_plus($this->source->path, $this->file);
			$image = $manager->make($path);
			return $image;
		};
		$image = $imagePromise();
		return $image;
	}

}
