<?php

class CheckSource extends AppController
{

	/**
	 * @var DBInterface
	 */
	protected $db;

	public function __construct(DBInterface $db)
	{
		parent::__construct();
		$this->db = $db;
		header('Access-Control-Allow-Origin: http://localhost:3000');
	}

	public function index()
	{
		$id = $this->request->int('id');
		$source = Source::findByID($this->db, $id);
		if (!$source) {
			throw new Exception('Source ' . $id . ' not found');
		}
		if (!is_dir($source->path)) {
			return new JSONResponse([
				'status' => 'error',
				'error' => 'no ' . $source->path
			]);
		}
		return new JSONResponse([
			'status' => 'ok',
			'files' => $source->files,
			'folders' => $source->folders,
		]);
	}

}
