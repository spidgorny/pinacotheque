<?php

/**
 * Class Source
 */
class Source extends POPOBase
{

	use DatabaseMixin;

	public $id;
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
