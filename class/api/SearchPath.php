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
		$like = new AsIsOp('MATCH (path) AGAINST (' . $this->db->quoteSQL($term).' IN NATURAL LANGUAGE MODE)');
		$files = MetaForSQL::findAll($this->db, [$like], 'ORDER BY '.$like.' DESC LIMIT 100');
		$xFiles = ArrayPlus::create($files);
		return new JSONResponse([
			'status' => 'ok',
			'term' => $term,
			'query' => $this->db->lastQuery.'',
			'files' => $xFiles->toJson(),
		]);
	}

}
