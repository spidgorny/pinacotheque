<?php

use pinacotheque\BackgroundProcess;


class Process extends AppController
{

	public function __construct()
	{
		parent::__construct();
	}

	public function __invoke()
	{
		$pid = $this->start();
//		$pid = 16844;
		$process = BackgroundProcess::createFromPID($pid);
		$this->watch($process);
		$process->stop();
	}

	public function start()
	{
		$process = new BackgroundProcess('sleep 500');
		$process->run();
		return $process->getPid();
	}

	public function watch(BackgroundProcess $process)
	{
		while ($process->isRunning()) {
			llog($process->getPid(), $process->getMem());
			sleep(1);
		}
		echo "\nDone.\n";
	}

}
