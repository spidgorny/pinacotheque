<?php

class GetUnscanned extends ApiController
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

		$this->db->perform("UPDATE files
SET ext = substr(path, -4)
WHERE type = 'file' AND ext is null");

		$provider = new FileProvider($this->db, $source);
		$filesToScan = $provider->getUnscanned();

		return json_encode([
			'status' => 'ok',
			'count' => count($filesToScan),
			'filesToScan' => $filesToScan,
		], JSON_THROW_ON_ERROR);
	}

}
