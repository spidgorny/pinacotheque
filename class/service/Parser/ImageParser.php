<?php

use Intervention\Image\Constraint;
use Intervention\Image\Exception\ImageException;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;

class ImageParser
{

	protected string $filePath;

	/** @var Image $image */
	protected Image $image;

	public array $log = [];

	public static function fromFile(string $filePath): ImageParser
	{
		$size = getimagesize($filePath);
//		debug($size);
		if ($size && ($size[0] > 20000 || $size[1] > 20000)) {
			throw new ImageException('Image too big ' . $size[0] . 'x' . $size[1]);
		}
		$manager = new ImageManager();
		$image = $manager->make($filePath);
		return new static($filePath, $image);
	}

	public function __construct(string $filePath, Image $image)
	{
		$this->filePath = $filePath;
		$this->image = $image;
	}

	public function log($message): void
	{
		$this->log[] = $message;
	}

	public function getMeta()
	{
		$meta = $this->image->exif();
//		if (error_get_last()) {
//			throw new Exception(error_get_last()['message']);
//		}
		if (!$meta) {
			$meta = $this->getMetaByIM();
		}
		if (!$meta) {
			$meta = $this->getMetaFromPHP();
		}
//		llog('meta keys', array_keys($meta));
		return $meta;
	}

	public function getMetaByIM()
	{
		try {
//			llog(__METHOD__, $this->filePath);
			$convertCommand = getenv('convert') ?: 'convert';
			$p = new Symfony\Component\Process\Process([$convertCommand, $this->filePath, 'json:-']);
//			llog($p->getCommandLine());
			$p->enableOutput();
			$p->start();
			$p->wait();
			if ($p->getErrorOutput()) {
				throw new Exception($p->getErrorOutput());
			}
			$json = $p->getOutput();
//			llog($json);
			$output = json_decode($json, false, 512, JSON_THROW_ON_ERROR);
			return $output[0]->image;
		} catch (Exception $e) {
			llog(get_class($e), $e->getMessage(), $e->getFile(), $e->getLine());
		}
	}

	public function getMetaFromPHP()
	{
		$meta = getimagesize($this->filePath);
		llog('getimagesize', $this->filePath, $meta);
		if ($meta) {
			$meta['width'] = $meta[0];
			$meta['height'] = $meta[1];
//			$meta['type'] = image_type_to_mime_type($meta[2]);
			unset($meta[0]);
			unset($meta[1]);
			unset($meta[2]);
			unset($meta[3]);    // width="xx" height="yy"
		}
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
