<?php

class Thumb
{

	/**
	 * @var Source
	 */
	protected $source;

	/**
	 * @var IMetaData
	 */
	protected $meta;

	public $log = [];

	public function __construct(IMetaData $meta)
	{
		$this->meta = $meta;
		$this->source = $meta->getSource();
	}

	/**
	 * @return string
	 * @deprecated
	 * @use $meta->getDestination()
	 */
	public function getThumbPath()
	{
		$dataStorage = getenv('DATA_STORAGE');
		$thumbPath = path_plus($dataStorage, $this->source->thumbRoot, $this->meta->getPath());
		if ($this->meta->isVideo()) {
			$ext = pathinfo($thumbPath, PATHINFO_EXTENSION);
			$thumbPath = str_replace_once('.' . $ext, '.png', $thumbPath);
		}
		return $thumbPath;
	}

	public function exists()
	{
		$thumbPath = $this->meta->getDestination();
		return file_exists($thumbPath);
	}

	public function getThumb(): string
	{
		if (!$this->exists()) {
			$tg = new ThumbGen($this->meta);
			$tg->generate();
		}
		$thumbPath = $this->meta->getDestination();
		return $thumbPath;
	}

	public function __debugInfo()
	{
		return [
			'getThumbPath' => $this->meta->getDestination(),
			'exists' => $this->exists(),
			'log' => $this->log,
		];
	}

	public function log(...$something)
	{
		llog(...$something);
	}

	public function getMeta()
	{
		$filename = $this->getThumb();
		$size = getimagesize($filename);
		$size['width'] = $size[0];
		$size['height'] = $size[1];
		return [
			'file' => stat($filename),
			'image'=> $size
		];
	}

}
