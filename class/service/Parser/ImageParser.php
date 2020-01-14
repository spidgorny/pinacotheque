<?php

use Intervention\Image\Constraint;
use Intervention\Image\Exception\ImageException;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;

class ImageParser
{

	/** @var Image $image */
	protected $image;

	public $log = [];

	public static function fromFile($filePath): ImageParser
	{
		$size = getimagesize($filePath);
//		debug($size);
		if ($size[0] > 20000 || $size[1] > 20000) {
			throw new ImageException('Image too big ' . $size[0] . 'x' . $size[1]);
		}
		$manager = new ImageManager();
		$image = $manager->make($filePath);
		return new static($image);
	}

	public function __construct(Image $image)
	{
		$this->image = $image;
	}

	public function log($message): void
	{
		$this->log[] = $message;
	}

	public function getMeta()
	{
		$meta = $this->image->exif();
//		llog('meta keys', sizeof($meta));
		return $meta;
	}

	public function saveThumbnailTo($destination): void
	{
//		echo 'Saving thumbnail to ', $destination, PHP_EOL;
		$start = microtime(true);
		$this->image->resize(256, null, static function (Constraint $constraint) {
			$constraint->aspectRatio();
		});

		$this->image->save($destination);
//		echo 'Saved in ', number_format(microtime(true) - $start, 3), PHP_EOL;
	}

	public function getCornerColors(): array
	{
		$colors = [];
		$width = $this->image->width();
		$height = $this->image->height();
		$colors[00] = $this->image->pickColor(0, 0);
		$colors[01] = $this->image->pickColor($width - 1, 0);
		$colors[10] = $this->image->pickColor(0, $height - 1);
		$colors[11] = $this->image->pickColor($width - 1, $height - 1);
		foreach ($colors as &$color) {
			unset($color[3]);    // alpha
		}
		return $colors;
	}

	public function getCornerColorsAsHex(): array
	{
		$cornerColors = $this->getCornerColors();
		$gradient = array_map(static function ($color) {
			return Color::fromRGBArray($color)->getCSS();
		}, $cornerColors);
		return $gradient;
	}

	public function getQuadrantColors(): array
	{
		$colors = [];
		$WWWW = $this->image->width() - 1;
		$halfW = floor($WWWW / 2);
		$HHHH = $this->image->height() - 1;
		$halfH = floor($HHHH / 2);

		$colors[00] = $this->getQuadrantColor(0, 0, $halfW, $halfH);
		$colors[01] = $this->getQuadrantColor($halfW, 0, $WWWW, $halfH);
		$colors[10] = $this->getQuadrantColor(0, $halfH, $halfW, $HHHH);
		$colors[11] = $this->getQuadrantColor($halfW, $halfH, $WWWW, $HHHH);

		return $colors;
	}

	public function getQuadrantColor($w1, $h1, $w2, $h2): array
	{
		$colors = [];
		for ($y = $h1; $y <= $h2; $y++) {
			for ($x = $w1; $x <= $w2; $x++) {
				$color = $this->image->pickColor($x, $y);
				$colors[] = $color;
			}
		}
		return Color::average($colors);
	}

	public function getQuadrantColorsAsHex(): array
	{
		$cornerColors = $this->getQuadrantColors();
		$gradient = array_map(static function ($color) {
			return Color::fromRGBArray($color)->getCSS();
		}, $cornerColors);
		return $gradient;
	}

}
