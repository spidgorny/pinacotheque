<?php

class ScanDirApi extends ApiController
{

	/**
	 * @var DBInterface
	 */
	protected DBInterface $db;

	public function __construct(DBInterface $db)
	{
		parent::__construct();
		$this->db = $db;
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

		header('Content-Type: application/json');

		$scanner = new \App\Service\ScanDir($this->db, $source);
		$scanner->progressCallback = [$this, 'report'];
		$scanner();

		return json_encode([
			'status' => 'done',
			'done' => true,
		], JSON_THROW_ON_ERROR);
	}

	function report($i, $max, $ok)
	{
		echo json_encode([
			'status' => 'line',
			'progress' => $i,
			'max' => $max,
			'res' => $ok,
		], JSON_THROW_ON_ERROR), PHP_EOL;
		flush();
	}

}
