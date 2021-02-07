<?php

class ProcessApi extends ApiController
{

	public function index()
	{
		$method = $this->request->getMethod();
		$params = $this->request->getURLLevels();
		array_shift($params);    // ProcessApi
		return $this->$method(...$params);
	}

	public function TEST(...$params)
	{
		llog($params);
		return new JSONResponse($params);
	}

	public function GET(int $pid = null)
	{
		llog('id', $pid);

		$pm = new ProcessManager();
		if ($pid) {
			$process = $pm->get($pid);
			return new JSONResponse([
				'pid' => $process->getPid(),
				'cmd' => $process->getCommand(),
				'mem' => $process->getMem(),
			]);
		}

		$pm->getAll();    // clear dead ones
		$pidList = $pm->pidList;

		return new JSONResponse([
			'id' => $pid,
			'pidList' => $pidList,
		]);
	}

	public function POST()
	{
		$cmd = $this->request->getPOST();
		if (!$cmd) {
			throw new Exception('No cmd provided');
		}

		$pm = new ProcessManager();
		$pm->run($cmd);
		$pidList = $pm->pidList;

		return new JSONResponse([
			'cmd' => $cmd,
			'pidList' => $pidList,
		]);
	}

	public function DELETE($pid)
	{
		if (!$pid) {
			throw new Exception('No pid');
		}

		$pm = new ProcessManager();
		$process = $pm->get($pid);
		if (!$process) {
			throw new Exception('Process with pid ' . $pid . ' not found');
		}
		$ok = $process->stop();

		return new JSONResponse([
			'pid' => $pid,
			'cmd' => $process->getCommand(),
			'ok' => $ok,
		]);
	}

}
