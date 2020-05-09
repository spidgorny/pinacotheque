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
		$where = [
			'DateTime ' => new SQLWhereNotEqual('DateTime', null),
		];
		if ($since) {
			$where['DateTime'] = new AsIsOp("<= '" . date('Y-m-d H:i:s', $since) . "'");
		}
		//return new JSONResponse($where);
		$files = MetaForSQL::findAll($this->db, $where, 'ORDER BY DateTime DESC LIMIT 100');
		$query = $this->db->getLastQuery();
//		return count($files);
		$files = new ArrayPlus($files);
		header('Access-Control-Allow-Origin: http://localhost:3000');
		return new JSONResponse([
			'status' => 'ok',
			'query' => $query . '',
			'data' => $files->toJson(),
		]);
	}

}
