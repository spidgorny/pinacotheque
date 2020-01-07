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
		$dataStorage = getenv('DATA_STORAGE');
		$thumbPath = path_plus($dataStorage, $this->source->thumbRoot, $this->meta->getPath());
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
		$this->prepareForSaving();
		$parser = ImageParser::fromFile($this->meta->getFullPath());
		$thumbPath = $this->getThumbPath();
		$parser->saveThumbnailTo($thumbPath);
	}

	public function prepareForSaving()
	{
		$thumbPath = $this->getThumbPath();
		$dirName = dirname($thumbPath);
		if (!is_dir($dirName)) {
			@mkdir($dirName, 0777, true);
		}
	}

	public function makeVideoThumb()
	{
		$this->prepareForSaving();
		$parser = VideoParser::fromFile($this->meta->getFullPath());
		$thumbPath = $this->getThumbPath();
		$parser->saveThumbnailTo($thumbPath);
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
