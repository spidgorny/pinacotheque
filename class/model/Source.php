<?php

/**
 * Class Source
 */
class Source extends POPOBase
{

	use DatabaseMixin;
	use DatabaseManipulation;

	public $id;
	public $name;
	public $path;
	public $thumbRoot;

	public static function getTableName()
	{
		return 'source';
	}

	/**
	 * Source constructor.
	 * @param $set
	 * @override
	 */
	public function __construct($set)
	{
		parent::__construct($set);
	}

	public function getFilesCount()
	{
		$files = $this->db->fetchOneSelectQuery('files', [
			'source' => $this->id,
		], '', 'count(*) as count');
		return $files['count'];
	}

}
