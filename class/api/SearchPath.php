<?php

class SearchPath extends AppController
{

	protected DBInterface $db;

	public function __construct(DBInterface $db)
	{
		parent::__construct();
		$this->db = $db;
		header('Access-Control-Allow-Origin: http://localhost:3000');
		header('Access-Control-Allow-Headers: content-type');
	}

	public function index()
	{
		$term = $this->request->getString('term');
//		$like = new SQLLike($term);
//		$like->wrap = '%|%';
		$like = new AsIs('MATCH (path) AGAINST (' . $this->db->quoteSQL($term).')');
		$files = MetaForSQL::findAll($this->db, ['path' => $like], 'LIMIT 100');
		$xFiles = ArrayPlus::create($files);
		return new JSONResponse([
			'status' => 'ok',
			'term' => $term,
			'files' => $xFiles->toJson(),
			'query' => $this->db->lastQuery.'',
		]);
	}

}
