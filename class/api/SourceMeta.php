<?php

class SourceMeta extends ApiController
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
		header('Content-Type: application/json');
	}

	public function index()
	{
		$id = $this->request->int('id');
		$source = Source::findByID($this->db, $id);
		if (!$source) {
			throw new Exception('Source ' . $id . ' not found');
		}

		if (!is_dir($source->path)) {
			throw new Exception('Source is not dir: ' . $source->path);
		}

		$filesInDB = $source->getFilesCount();

		return new JSONResponse([
			'status' => 'ok',
			'filesInDB' => $filesInDB,
		]);
	}

}
