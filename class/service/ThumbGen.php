<?php

use Intervention\Image\Exception\NotReadableException;

class ThumbGen
{

	public MetaForSQL $file;

	public function __construct(MetaForSQL $file)
	{
		$this->file = $file;
	}

	protected function log(...$a)
	{
		llog(...$a);
	}

	public function generate()
	{
		$destination = $this->file->getDestination();
		if (file_exists($destination)) {
			$this->log('Thumb', 'exists', new Bytes(filesize($this->file->getDestination())).'');
			$thumb = $this->file->getDestination();
		} else {
			$thumb = $this->fetchThumbnail();
		}
		return $thumb;
	}

	public function fetchThumbnail()
	{
		$thumbPath = $this->file->getDestination();
		try {
			$this->makeThumb();    // make it if doesn't exist
			$this->log('Thumb', 'OK', new Bytes(@filesize($this->file->getDestination())).'');
		} catch (NotReadableException $e) {
			$this->log('** Error:', get_class($e), $e->getMessage(), $e->getFile(), $e->getLine());
			$this->log('Thumb', '*** FAIL ***');
		}
		return $thumbPath;
	}

	public function makeThumb()
	{
		if ($this->file->isImage()) {
			$this->makeImageThumb();
		} elseif ($this->file->isVideo()) {
			$this->makeVideoThumb();
		}
	}

	public function makeImageThumb()
	{
		$this->prepareForSaving();
		$parser = ImageParser::fromFile($this->file->getFullPath());
		$thumbPath = $this->file->getDestination();
		$parser->saveThumbnailTo($thumbPath);
		$this->log($parser->log);
		return true;	// unless Exception
	}

	public function prepareForSaving()
	{
		$thumbPath = $this->file->getDestination();
		$dirName = dirname($thumbPath);
		if (!is_dir($dirName)) {
			$this->log('mkdir:', $dirName);
			$ok = mkdir($dirName, 0777, true);
			if (!$ok) {
				$this->log('mkdir failed [' . $dirName . ']');
			}
		}
	}

	public function makeVideoThumb()
	{
		$this->prepareForSaving();
		$parser = VideoParser::fromFile($this->file->getFullPath());
		$thumbPath = $this->file->getDestination();
		$ok = $parser->saveThumbnailTo($thumbPath);
		$this->log($parser->log);
		return $ok;
	}

}
