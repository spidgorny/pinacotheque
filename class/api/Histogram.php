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
	}

	public function index(): JSONResponse
	{
		$provider = new FileProvider($this->db);
		$histogram = $provider->getHistogram();
		return new JSONResponse([
			'status' => 'ok',
			'query' => $this->db->getLastQuery().'',
			'histogram' => $histogram,
		]);
	}

}
