<?php

namespace pinacotheque;

class BackgroundProcess extends \Cocur\BackgroundProcess\BackgroundProcess
{

	/**
	 * @var string
	 */
	protected ?string $command;

	/**
	 * @var int
	 */
	protected int $pid;

	public static function createFromPID($pid) {
		$process = new static();
		$process->setPid($pid);

		return $process;
	}

	public function __construct(string $command = null)
	{
		parent::__construct($command);
		$this->command = $command;
		$this->serverOS = $this->getOS();
	}

	protected function checkSupportingOS($message)
	{
		// ok
	}

	public function run($outputFile = '/dev/null', $append = false)
	{
		if ($this->getOS() !== self::OS_WINDOWS) {
			parent::run($outputFile, $append);
			return $this->getPid();
		}

		// Windows
//		shell_exec(sprintf('%s &', $this->command, $outputFile));

		llog('self', getmypid());

		// https://stackoverflow.com/questions/3679663/how-to-get-pid-from-php-function-exec-in-windows
		$descriptorspec = [
			0 => ["pipe", "r"],
			1 => ["pipe", "w"],
		];
		$prog = proc_open("start /b " . $this->command . ' > ' . $outputFile, $descriptorspec, $pipes);
		if (!is_resource($prog)) {
			throw new \Exception('No prog');
		}

		$ppid = proc_get_status($prog);
//		llog($ppid);
		$pid = $ppid['pid'];
		$wmic = "wmic process get parentprocessid,processid | find \"$pid\"";
//		llog($wmic);
		$output = array_filter(explode(" ", shell_exec($wmic)));
//		llog($output);
		array_pop($output);

		// if pid exitst this will not be empty
		$pid = end($output);

		$this->setPid($pid);
		$this->pid = $pid;
		return $this->pid;
	}

	public function isRunning()
	{
		if ($this->serverOS !== self::OS_WINDOWS) {
			return parent::isRunning();
		}

		// Windows
		try {
			$result = shell_exec(sprintf('tasklist /nh /fi "PID eq %d" 2>&1', $this->getPid()));
			if (str_contains($result, $this->getPid())) {
				return true;
			}
		} catch (\Exception $e) {
		}

		return false;
	}

	public function stop()
	{
		if ($this->getOS() !== self::OS_WINDOWS) {
			return parent::stop();
		}

		// Windows
		return shell_exec(sprintf('taskkill /f /PID %d 2>&1', $this->getPid()));
	}

	public function getMem()
	{
		$result = shell_exec(sprintf('tasklist /nh /fi "PID eq %d" /fo csv', $this->getPid()));
		$lines = trimExplode("\n", $result);
		$csv = str_getcsv($lines[0]);
		$memWithK = $csv[4];
		$mem = parseFloat($memWithK);
		$lastChar = $memWithK[strlen($memWithK) - 1];
		if ($lastChar === 'K') {
			$mem *= 1024;
		}
		if ($lastChar === 'M') {
			$mem *= 1024 * 1024;
		}
		if ($lastChar === 'G') {
			$mem *= 1024 * 1024 * 1024;
		}
		return floor($mem);
	}

	public function setCommand(string $cmd)
	{
		$this->command = $cmd;
	}

	public function getCommand()
	{
		return $this->command;
	}

}
