<?php

use Intervention\Image\Constraint;
use Intervention\Image\Image;

class ImageParser
{

	/** @var Image $image */
	protected $image;

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
		$this->image->resize(256, null, function (Constraint $constraint) {
			$constraint->aspectRatio();
		});

		$this->image->save($destination);
	}

}
