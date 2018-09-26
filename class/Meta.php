<?php

/**
 * Class Meta - represents a single file metadata from meta.json
 * @property string _path_
 * @property string FileName
 * @property int FileDateTime
 * @property array COMPUTED
 */
class Meta
{

	protected $props = [];

	public function __construct(array $meta)
	{
		$this->props = $meta;
	}

	public function __get($key)
	{
		return ifsetor($this->props[$key]);
	}

	public function getThumbnail($prefix = '')
	{
		$src = $prefix.'/'.$this->_path_.'/'.$this->props['FileName'];
		return $src;
	}

	public function toHTML($prefix = '')
	{
		return HTMLTag::img($this->getThumbnail($prefix), [
			'width' => 256,
		]);
	}

	public function width()
	{
		return $this->COMPUTED['Width'];
	}

	public function height()
	{
		return $this->COMPUTED['Height'];
	}

	public function getOriginal($prefix = '')
	{
		$path = str_replace('__', ':/', $this->_path_);
		$path = trimExplode('_', $path);
		$path = implode('/', $path);
		$path = new Path($path);
		return $prefix.'/'.$path->getURL().''.$this->FileName;
	}

	public function __debugInfo()
	{
		return $this->props;
	}

}
