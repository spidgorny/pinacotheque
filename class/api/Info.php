<?php

class Info extends AppController
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
		$provider = new FileProvider($this->db);
		list('min' => $min, 'max' => $max) = $provider->getMinMax();
		header('Access-Control-Allow-Origin: http://localhost:3000');
		return new JSONResponse([
			'status' => 'ok',
			'query' => $this->db->getLastQuery().'',
			'min' => $min,
			'max' => $max,
			'sources' => Source::findAll($this->db),
		]);
	}

}
