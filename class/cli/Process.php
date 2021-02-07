<?php

use Cocur\BackgroundProcess\BackgroundProcess;


class Process extends AppController
{

	public function __construct()
	{
		parent::__construct();
	}

	public function __invoke()
	{
		$process = new BackgroundProcess('sleep 5');
		$process->run();
		echo sprintf('Crunching numbers in process %d', $process->getPid());
		while ($process->isRunning()) {
			echo '.';
			sleep(1);
		}
		echo "\nDone.\n";
	}

}
