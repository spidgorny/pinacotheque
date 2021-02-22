<?php

class TagModel extends POPOBase
{

	use DatabaseMixin;
	use DatabaseManipulation;

	public int $id;
	public int $id_file;
	public string $tag;
	public DateTime $tstamp;

	public array $props = [];

	public static function getTableName()
	{
		return 'tag';
	}

	public function __construct($set)
	{
		parent::__construct($set);
		$this->props = $set;
	}

	public function toJson()
	{
		return (object)($this->props);
	}

}
