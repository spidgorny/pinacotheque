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

		try {
			$file = MetaForSQL::findOne($this->db, [
				'source' => $source,
				'path' => $path,
			]);
			if (!$file) {
//				throw new Error('No db entry for [' . $path .']');
				$file = new MetaForSQL([
					'id' => -1,
					'source' => $source,
					'path' => '',
					'type' => 'dir',
				]);
				$file->injectDB($this->db);
			}
			$folder = $file->getFolder();
			if (!$folder) {
				throw new Exception('No way to convert this file to folder');
			}
			$files = $folder->getFiles($this->pageSize, $offset);
			$query = $folder->getQuery();
			$countQuery = $folder->getCountQuery();
			$rows = $countQuery->getCount();
			return new JSONResponse([
				'status' => 'ok',
				'path' => $path,
				'offset' => $offset,
				'file' => $file->toJson(),
				'folder' => $folder ? $folder->toJson() : null,
				'isTypeFile' => $folder->isTypeFile(),
				'isTypeDir' => $folder->isTypeDir(),
				'query' => $query . '',
				'rows' => $rows,
				'countQuery' => $countQuery->countQuery,
				'data' => array_values($files->toJson()),
				'nextOffset' => ($offset + $this->pageSize) < $rows ? $offset + $this->pageSize : null,
			]);
		} catch (Exception $e) {
//			$where = $folder->getWhere();
//			$orderBy = $folder->getOrderBy();
//			$query = $this->db->getSelectQuery(MetaForSQL::getTableName(), $where, $orderBy);
//			llog('queryParams', $query->getParameters());
//			throw new Exception($e->getMessage() . ' ['.$query.']');
			throw $e;
		}
	}

}
