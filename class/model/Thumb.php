<?php

use Intervention\Image\Constraint;
use Intervention\Image\Exception\NotReadableException;
use Intervention\Image\ImageManager;
use Symfony\Component\Process\Process;

class Thumb
{

	/**
	 * @var Source
	 */
	protected $source;

	/**
	 * @var MetaForSQL
	 */
	protected $meta;

	public function __construct(MetaForSQL $meta)
	{
		$this->meta = $meta;
		$this->source = $meta->getSource();
	}

	public function getThumbPath()
	{
		$thumbPath = __DIR__ . '/../../data/' . $this->source->thumbRoot . '/' . $this->meta->getPath();
		if ($this->meta->isVideo()) {
			$ext = pathinfo($thumbPath, PATHINFO_EXTENSION);
			$thumbPath = str_replace($ext, 'png', $thumbPath);
		}
		return $thumbPath;
	}

	public function exists()
	{
		$thumbPath = $this->getThumbPath();
		return file_exists($thumbPath);
	}

	public function getThumb()
	{
		if (!$this->exists()) {
			$this->makeThumb();
		}
		$thumbPath = $this->getThumbPath();
		return $thumbPath;
	}

	public function makeThumb()
	{
		if ($this->meta->isImage()) {
			$this->makeImageThumb();
		} elseif ($this->meta->isVideo()) {
			$this->makeVideoThumb();
		}
	}

	public function makeImageThumb()
	{
		$manager = new ImageManager();
		$image = $manager->make($this->meta->getFullPath());
		$image->resize(256, null, function (Constraint $constraint) {
			$constraint->aspectRatio();
		});

		$thumbPath = $this->getThumbPath();
		$this->prepareForSaving();
		$image->save($thumbPath);
	}

	public function prepareForSaving()
	{
		$thumbPath = $this->getThumbPath();
		$dirName = dirname($thumbPath);
		if (!is_dir($dirName)) {
			mkdir($dirName, 0777, true);
		}
	}

	public function makeVideoThumb()
	{
		$this->prepareForSaving();
		$ffmpeg = getenv('ffmpeg');
		$thumbPath = $this->getThumbPath();
		$cmd = [$ffmpeg, '-i', $this->meta->getFullPath(), '-ss', '00:00:01.000', '-vframes', '1', $thumbPath];
		$p = new Process($cmd);
		$p->run();
		if ($p->getExitCode()) {
			$error = $p->getErrorOutput();
			debug($cmd);
			debug($error);
		}
	}

	public function __debugInfo()
	{
		return [
			'getThumbPath' => $this->getThumbPath(),
			'exists' => $this->exists(),
		];
	}

}
