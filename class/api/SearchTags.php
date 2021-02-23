<?php

class SearchTags extends AppController
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

		// TODO: query JOIN file directly to be faster
		$terms = TagModel::findAll($this->db, [
			'tag' => $term
		]);
		$xTerms = ArrayPlus::create($terms);
		$xFiles = $xTerms->map( function ($el) {
			return MetaForSQL::findOne($this->db, ['id' => $el->id_file]);
		})->unique();
		return new JSONResponse([
			'status' => 'ok',
			'term' => $term,
			'files' => $xFiles->toJson(),
		]);
	}

}
