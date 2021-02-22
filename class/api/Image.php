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
		$files = [];
		if ($file) {
			$folder = $file->getFolder();
			$files = $folder->getFiles(999999)->getData();
		}
		return new JSONResponse([
			'status' => 'ok',
			'file' => $file->toJson(),
			'rows' => count($files),
			'folder' => array_map(static function ($el) {
				return $el->id;
			}, $files),
			'query' => $folder ? $folder->getQuery().'' : null,
		]);
	}

}
