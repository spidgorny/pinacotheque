<?php

use Intervention\Image\Image;

class ImageParser
{

	/** @var Image $image */
	protected $image;

	public function __construct(Image $image)
	{
		$this->image = $image;
	}

}
