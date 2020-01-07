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

	public $log = [];

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
		$parser = new ImageParser($image);

		$thumbPath = $this->getThumbPath();
		$parser->saveThumbnailTo($thumbPath);
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
		$time = '00:00:01.000';
		$probe = $this->probe();
		if ($probe->format->duration < 1) {
			$time = '00:00:00.000';
		}
		$ffmpeg = getenv('ffmpeg');
		$thumbPath = $this->getThumbPath();
		$cmd = [$ffmpeg, '-i', $this->meta->getFullPath(), '-ss', $time, '-vframes', '1', '-vf', 'scale=256:-1', $thumbPath];
		$this->log($cmd);
		$p = new Process($cmd);
		$p->run();
		if ($p->getExitCode()) {
			$error = $p->getErrorOutput();
			debug($cmd);
			debug($error);
		}
	}

	public function probe()
	{
		$ffmpeg = getenv('ffmpeg');
		$ffprobe = str_replace('ffmpeg.exe', 'ffprobe.exe', $ffmpeg);
		$thumbPath = $this->getThumbPath();
		$cmd = [$ffprobe, '-v', 'quiet', '-print_format', 'json',  '-show_format', '-show_streams', $this->meta->getFullPath()];
		$this->log(implode(' ', $cmd));
		$p = new Process($cmd);
		$p->run();
		return json_decode($p->getOutput());
	}

	public function __debugInfo()
	{
		return [
			'getThumbPath' => $this->getThumbPath(),
			'exists' => $this->exists(),
			'log' => $this->log,
		];
	}

	public function log($something)
	{
		$this->log[] = $something;
	}

}
