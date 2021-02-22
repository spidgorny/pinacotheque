<?php

namespace Pinacotheque\Model;

use MetaForSQL;

class Folder extends MetaForSQL
{

	public function __construct(array $meta)
	{
		parent::__construct($meta);
//		llog($this->source, $this->path);
	}

	public function getFiles($pageSize = 50, $offset = 0)
	{
		$where = $this->getWhere();
		$orderBy = $this->getOrderBy($pageSize, $offset);
		$files = MetaForSQL::findAll($this->db, $where, $orderBy);
//		$query = $this->db->getLastQuery();
		$files = new \ArrayPlus($files);
		$files->filter(/**
		 * @param MetaForSQL $el
		 * @return bool
		 */ static function (MetaForSQL $el) {
			return $el->isDir() || $el->isImage() || $el->isVideo();
		});
		return $files;
	}

	public function getQuery()
	{
		return $this->db->getSelectQuery(self::getTableName(), $this->getWhere(), $this->getOrderBy());
	}

	public function getCountQuery()
	{
		$query = $this->getQuery();
		$countQuery = new \SQLCountQuery($query, $this->db);
		return $countQuery;
	}

	public function getWhere()
	{
//		assert($this->path);
//		llog($this->path);
		return $this->getWhereFor($this->source, $this->path);
	}

	public function getOrderBy($pageSize = 50, $offset = 0)
	{
		return 'ORDER BY type, path LIMIT '.$pageSize.' OFFSET ' . (int)$offset;
	}

	/**
	 * Search for files inside a single folder
	 * without subfolders
	 * @param int|null $source
	 * @param string $path
	 * @return \SQLWhereNotEqual[]
	 */
	public function getWhereFor(?int $source, string $path): array
	{
		$where = [];
		if ($source) {
			$where['source'] = $source;
		}
		if ($path) {
			$like = new \SQLLike($path);
			$like->wrap = '|/%';
			$where['path'] = $like;
		} else {
			$like = new \SQLLike($path);
			$like->like = 'NOT LIKE';
			$like->wrap = '|%/%';
			$where['path'] = $like;
		}
		$notLike = new \SQLLike($path);
		$notLike->like = 'NOT LIKE';
		$notLike->wrap = '|/%/%';
		$where['path '] = $notLike;
		return $where;
	}

}
