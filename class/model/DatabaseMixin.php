<?php


trait DatabaseMixin
{

	/**
	 * @var DBLayerSQLite
	 */
	protected $db;

	public static function getTableName()
	{
		return null;
	}

	public static function findByID(DBLayerSQLite $db, $id)
	{
		$row = $db->fetchOneSelectQuery(static::getTableName(), [
			'id' => $id,
		]);
		$instance = new static($row);
		$instance->db = $db;
		return $instance;
	}

}
