<?php

class Histogram extends ApiController
{

	/**
	 * @var DBInterface
	 */
	protected DBInterface $db;

	public function __construct(DBInterface $db)
	{
		parent::__construct();
		$this->db = $db;
		header('Access-Control-Allow-Origin: http://localhost:3000');
		$this->request->setCacheable(3600);
	}

	public function index(): JSONResponse
	{
		$provider = new FileProvider($this->db);
		$histogram = $provider->getHistogram();
		$assoc = [];
		$sum = 0;
		foreach ($histogram as $row) {
			$assoc[$row['date(DateTime)']] = $row['images'];
			$sum += $row['images'];
		}
		$stats = $provider->getStats();
		$files = $stats['files']['TABLE_ROWS'];

		return new JSONResponse([
			'status' => 'ok',
			'query' => $this->db->getLastQuery() . '',
			'sum' => $sum,
			'files' => $files,
			'histogram' => $assoc,
		]);
	}

}
