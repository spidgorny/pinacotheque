<?php

class Images extends AppController
{

	/**
	 * @var DBInterface
	 */
	protected $db;

	public function __construct(DBInterface $db)
	{
		parent::__construct();
		$this->db = $db;
	}

	public function index()
	{
		$since = $this->request->getTimestampFromString('since');
		$where = $this->getWhere($since);
		//return new JSONResponse($where);
		$files = $this->getFiles($where);
		$query = $this->db->getLastQuery();
//		return count($files);
		$files = new ArrayPlus($files);
		header('Access-Control-Allow-Origin: http://localhost:3000');
		return new JSONResponse([
			'status' => 'ok',
			'since' => $since,
			'query' => $query . '',
			'data' => $files->toJson(),
		]);
	}

	/**
	 * @param int $since
	 * @return SQLWhereNotEqual[]
	 */
	public function getWhere(int $since): array
	{
		$where = [
			'DateTime ' => new SQLWhereNotEqual('DateTime', null),
		];
		if ($since) {
			$where['DateTime'] = new AsIsOp("<= '" . date('Y-m-d H:i:s', $since) . "'");
		}
		return $where;
	}

	/**
	 * @param array $where
	 * @return array
	 */
	public function getFiles(array $where): array
	{
		$files = MetaForSQL::findAll($this->db, $where, 'ORDER BY DateTime DESC LIMIT 10');
		return $files;
	}

}