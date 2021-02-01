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
			$source->path = '/c/windows/assembly';
		} else {
			if (!is_dir($source->path)) {
				throw new Exception('Source is not dir: ' . $source->path);
			}
		}

		$find = getenv('find') ?: 'find';
		$cmd = [$find, $source->path, '-type', 'd'];
//		$cmd = "echo asd";
		llog($cmd);

//		return $this->waitPush($source, $cmd);
		return $this->stream($source, $cmd);
	}

	function waitPush(Source $source, array $cmd)
	{
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

	function stream(Source $source, array $cmd)
	{
		set_time_limit(0);
		$p = new Process($cmd);
		$p->enableOutput();

		header('Access-Control-Allow-Origin: http://localhost:3000');
		header('Content-type: application/json');

		$allLines = [];
		$p->run(function ($type, $buffer) use (&$allLines) {
			$plus = $this->streamLines($buffer, Process::ERR === $type ? 'err' : 'lines');
			$allLines = array_merge($plus, $allLines);
		});

		$md5 = md5(implode(PHP_EOL, $allLines));

		$source->update([
			'folders' => count($allLines),
			'md5' => $md5,
			'mtime' => new SQLNow(),
		]);

		return json_encode([
			'status' => 'ok',
			'done' => true,
			'md5' => $md5,
			'folders' => count($allLines),
		], JSON_THROW_ON_ERROR, JSON_PRETTY_PRINT);
	}

	/// sometimes the buffer is split in the middle of the line
	/// we store the cut line and use it for the next time
	function streamLines(string $buffer, string $type)
	{
		static $lastLine;
		if ($lastLine) {
			$buffer = $lastLine . $buffer;
		}
		$lines = trimExplode("\n", $buffer);

		if (!str_endsWith($buffer, "\n")) {
			$lastLine = array_pop($lines);    // incomplete
		}

		echo json_encode([
			'status' => $type,
			'file' => $lines,
		], JSON_THROW_ON_ERROR), PHP_EOL;
		flush();
		ob_flush();
		return $lines;
	}

}
