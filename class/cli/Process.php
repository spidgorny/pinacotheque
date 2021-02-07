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
		$this->testFileCache();
//		$this->testAPCU();
//		$this->runOnce();
	}

	public function testFileCache()
	{
		$fc = new FileCache(null, sys_get_temp_dir());

//		$rand = mt_rand();
//		llog('rand', $rand);
//		$fc->set('pid', [$rand, $rand]);

		$pid = $fc->get('pid');
		llog('pid', $pid);
	}

	public function testAPCU()
	{
		$rand = mt_rand();
//		llog('rand', $rand);
//		apcu_store('pid', $rand);
		$pid = apcu_fetch('pid');
		llog('pid', $pid);
	}

	public function runOnce()
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
