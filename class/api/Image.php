<?php

class Image extends AppController
{

	/** @var DBInterface */
	protected DBInterface $db;

	public function __construct(DBInterface $db)
	{
		parent::__construct();
		$this->db = $db;
		header('Access-Control-Allow-Origin: http://localhost:3000');
	}

	public function index()
	{
		$id = $this->request->getIntRequired('id');
		$file = MetaForSQL::findOne($this->db, [
			'id' => $id,
		]);
		if ($file) {
			$file->loadMeta();
			$file->loadTags();
		}
		return new JSONResponse([
			'status' => 'ok',
			'file' => $file,
		]);
	}

}
