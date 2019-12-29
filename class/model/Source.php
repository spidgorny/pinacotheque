<?php

/**
 * Class Source
 */
class Source extends POPOBase
{

	use DatabaseMixin;
//	use DatabaseManipulation;

	public $id;
	public $name;
	public $path;
	public $thumbRoot;

	public static function getTableName()
	{
		return 'source';
	}

	public function __construct($set)
	{
		parent::__construct($set);
	}

}