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

		$scanner = new \App\Service\ScanDir($this->db, $source);
		$scanner->progressCallback = [$this, 'report'];
		$inserted = $scanner();

		return json_encode([
			'status' => 'done',
			'done' => true,
			'inserted' => $inserted,
		], JSON_THROW_ON_ERROR);
	}

	function report(string $file, int $i, int $max, string $ok)
	{
		echo json_encode([
			'status' => 'line',
			'file' => $file,
			'progress' => $i,
			'max' => $max,
			'res' => $ok,
		], JSON_THROW_ON_ERROR), PHP_EOL;
		flush();
	}

}
