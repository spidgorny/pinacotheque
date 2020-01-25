<?php

class TagModel extends POPOBase
{

	use DatabaseMixin;
	use DatabaseManipulation;

	public $id;
	public $id_file;
	public $tag;
	public $tstamp;

	public static function getTableName()
	{
		return 'tag';
	}

	public function __construct($set)
	{
		parent::__construct($set);
	}

}
