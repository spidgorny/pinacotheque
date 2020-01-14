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

	public function getThumb()
	{
		if (!$this->exists()) {
			$this->makeThumb();
		}
		$thumbPath = $this->meta->getDestination();
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
		$thumbPath = $this->meta->getDestination();
		$ok = $parser->saveThumbnailTo($thumbPath);
		$this->log($parser->log);
		return $ok;
	}

	public function prepareForSaving()
	{
		$thumbPath = $this->meta->getDestination();
		$dirName = dirname($thumbPath);
		if (!is_dir($dirName)) {
			$this->log('mkdir: ' . $dirName);
			$ok = mkdir($dirName, 0777, true);
			if (!$ok) {
				$this->log('mkdir failed ' . $ok);
			}
		}
	}

	public function makeVideoThumb()
	{
		$this->prepareForSaving();
		$parser = VideoParser::fromFile($this->meta->getFullPath());
		$thumbPath = $this->meta->getDestination();
		$ok = $parser->saveThumbnailTo($thumbPath);
		$this->log($parser->log);
		return $ok;
	}

	public function __debugInfo()
	{
		return [
			'getThumbPath' => $this->meta->getDestination(),
			'exists' => $this->exists(),
			'log' => $this->log,
		];
	}

	public function log($something)
	{
		$this->log[] = $something;
	}

}
