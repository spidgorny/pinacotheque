<?php

/**
 * Class Meta - represents a single file metadata from meta.json
 * @property string _path_
 * @property string FileName
 * @property int FileDateTime
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

	public function toHTML($prefix = '')
	{
		$src = $prefix.'/'.$this->_path_.'/'.$this->props['FileName'];
		return HTMLTag::img($src, [
			'width' => 256,
		]);
	}

}
