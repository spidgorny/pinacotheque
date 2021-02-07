<?php

use pinacotheque\BackgroundProcess;

class ProcessManager
{

	public array $pidList = [];
	protected FileCache $fc;

	public function __construct()
	{
		$this->fc = new FileCache(null, sys_get_temp_dir());
		$file = $this->fc->map('pid');
		llog('file', $file);
		if (is_file($file)) {
			$data = file_get_contents($file);
			llog('data', $data);
		}
		$this->pidList = (array)$this->fc->get('pid');
	}

	public function getAll(): array
	{
		$procList = array_map(function ($pid) {
			llog('pid', $pid);
			return $this->get($pid);
		}, array_keys($this->pidList));
		$procList = array_filter($procList);
		return $procList;
	}

	public function get(int $pid): ?BackgroundProcess
	{
		$process = BackgroundProcess::createFromPID($pid);
		$cmd = $this->pidList[$pid] ?? null;
		if (!$cmd) {
			unset($this->pidList[$pid]);
			$this->fc->set('pid', $this->pidList);
			return null;
		}
		$process->setCommand($cmd);
		if (!$process->isRunning()) {
			unset($this->pidList[$process->getPid()]);
			$this->fc->set('pid', $this->pidList);
		}
		return $process;
	}

	public function run(string $cmd)
	{
		$process = new BackgroundProcess($cmd);
		$pid = $process->run($cmd);
		$pid = (int)$pid;
		$this->pidList[$pid] = $cmd;
		$this->fc->set('pid', $this->pidList);
	}

}
