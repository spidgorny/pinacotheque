<?php

class Folder extends AppController
{

	/**
	 * @var DBInterface
	 */
	protected DBInterface $db;

	protected int $pageSize = 50;

	public function __construct(DBInterface $db)
	{
		parent::__construct();
		$this->db = $db;
		header('Access-Control-Allow-Origin: http://localhost:3000');
	}

	public function index()
	{
		$source = $this->request->getInt('source');
		$path = $this->request->get('path', '');
		$offset = $this->request->getInt('offset', 0);

		$folder = MetaForSQL::findOne($this->db, [
			'path' => $path,
		]);

		$where = $this->getWhere($source, $path);
		$orderBy = 'ORDER BY type, path LIMIT '.$this->pageSize.' OFFSET ' . (int)$offset;
		try {
			$files = MetaForSQL::findAll($this->db, $where, $orderBy);
			$query = $this->db->getLastQuery();
	//		return count($files);
			$files = new ArrayPlus($files);
			$files->filter(/**
			 * @param MetaForSQL $el
			 * @return bool
			 */ static function (MetaForSQL $el) {
				return $el->isDir() || $el->isImage() || $el->isVideo();
			});
			return new JSONResponse([
				'status' => 'ok',
				'path' => $path,
				'offset' => $offset,
				'folder' => $folder ? $folder->toJson() : null,
				'query' => $query . '',
				'data' => array_values($files->toJson()),
				'nextOffset' => $offset + $this->pageSize,
			]);
		} catch (Exception $e) {
			$query = $this->db->getSelectQuery(MetaForSQL::getTableName(), $where, $orderBy);
			llog('queryParams', $query->getParameters());
			throw new Exception($e->getMessage() . ' ['.$query.']');
		}
	}

	/**
	 * Search for files inside a single folder
	 * without subfolders
	 * @param int|null $source
	 * @param string $path
	 * @return SQLWhereNotEqual[]
	 */
	public function getWhere(?int $source, string $path): array
	{
		$where = [];
		if ($source) {
			$where['source'] = $source;
		}
		if ($path) {
			$like = new SQLLike($path);
			$like->wrap = '|/%';
			$where['path'] = $like;
		} else {
			$like = new SQLLike($path);
			$like->like = 'NOT LIKE';
			$like->wrap = '|%/%';
			$where['path'] = $like;
		}
		$notLike = new SQLLike($path);
		$notLike->like = 'NOT LIKE';
		$notLike->wrap = '|/%/%';
		$where['path '] = $notLike;
		return $where;
	}

}
