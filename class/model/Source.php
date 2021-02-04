<?php

/**
 * Class Source
 */
class Source extends POPOBase
{

	use DatabaseMixin;
	use DatabaseManipulation;

	public const NAME = 'name';
	public const PATH = 'path';

	public int $id;
	public string $name;
	public string $path;
	public string $thumbRoot;
	public ?int $files;
	public ?int $folders;
	public ?string $md5;	// of folders
	public ?DateTimeImmutable $mtime;
	public ?int $inserted;

	public static function getTableName()
	{
		return 'source';
	}

	public static function findByName(DBInterface $db, $name)
	{
		$row = $db->fetchOneSelectQuery(static::getTableName(), [
			static::NAME => $name,
		]);
//		debug($row);
		if (!$row) {
			return null;
		}
		$instance = new static($row);
		$instance->db = $db;
		return $instance;
	}

	public static function findByPath(DBInterface $db, $path)
	{
		$row = $db->fetchOneSelectQuery(static::getTableName(), [
			static::PATH => $path,
		]);
//		debug($row);
		if (!$row) {
			return null;
		}
		$instance = new static($row);
		$instance->db = $db;
		return $instance;
	}

	/**
	 * Source constructor.
	 * @param $set
	 * @override
	 * @throws Exception
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

	/**
	 * @param array $where
	 * @param string $orderBy
	 * @return MetaForSQL[]
	 */
	public function getFiles($where = [], $orderBy = ''): array
	{
		return MetaForSQL::findAll($this->db, $where + [
			'type' => 'file',
			'source' => $this->id,
		], $orderBy);
	}

}
