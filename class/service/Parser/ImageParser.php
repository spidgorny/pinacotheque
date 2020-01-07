<?php

use Intervention\Image\Constraint;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;

class ImageParser
{

	/** @var Image $image */
	protected $image;

	public static function fromFile($filePath): ImageParser
	{
		$manager = new ImageManager();
		$image = $manager->make($filePath);
		return new static($image);
	}

	public function __construct(Image $image)
	{
		$this->image = $image;
	}

	public function getMeta()
	{
		$meta = $this->image->exif();
//		llog('meta keys', sizeof($meta));
		return $meta;
	}

	public function saveThumbnailTo($destination)
	{
//		echo 'Saving thumbnail to ', $destination, PHP_EOL;
		$start = microtime(true);
		$this->image->resize(256, null, function (Constraint $constraint) {
			$constraint->aspectRatio();
		});

		$this->image->save($destination);
//		echo 'Saved in ', number_format(microtime(true) - $start, 3), PHP_EOL;
	}

}