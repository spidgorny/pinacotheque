<?php

use Symfony\Component\Process\Process;

class SourceScan extends ApiController
{

	/**
	 * @var DBInterface
	 */
	protected $db;

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

		if (gethostname() === '761K7Y2') {
			$source->path = '/c/windows';
		} else {
			if (!is_dir($source->path)) {
				throw new Exception('Source is not dir: ' . $source->path);
			}
		}

		$find = getenv('find') ?? 'find';
		$cmd = [$find, $source->path, '-type', 'f'];
//		$cmd = "echo asd";
		llog($cmd);
		$p = new Process($cmd);
		$p->enableOutput();
		$p->start();
		$p->wait();
		if ($p->getErrorOutput()) {
			throw new Exception($p->getErrorOutput());
		}
		$files = trimExplode("\n", $p->getOutput());
		header('Access-Control-Allow-Origin: http://localhost:3000');
		return new JSONResponse([
			'status' => 'ok',
			'files' => $files,
		]);
	}

}
