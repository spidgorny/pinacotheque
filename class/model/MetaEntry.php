<?php

/**
 * Class MetaEntry
 */
class MetaEntry extends POPOBase
{

	use DatabaseMixin;
	use DatabaseManipulation;

	public $id;
	public $id_file;
	public $name;
	public $value;

	public static function getTableName(): string
	{
		return 'meta';
	}

	/** Required */
	public function __construct($set)
	{
		parent::__construct($set);
	}

}
