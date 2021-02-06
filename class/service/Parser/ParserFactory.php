<?php

class ParserFactory
{

	protected MetaForSQL $file;

	public static function getInstance(MetaForSQL $file)
	{
		return new self($file);
	}

	public function __construct(MetaForSQL $file)
	{
		$this->file = $file;
	}

	public function getParser()
	{
		if ($this->file->isImage()) {
			return ImageParser::fromFile($this->file->getFullPath());
		}

		if ($this->file->isVideo()) {
			return VideoParser::fromFile($this->file->getFullPath());
		}

		throw new Exception('Unknown file format for '. basename($this->file->getPath()));
	}

}
