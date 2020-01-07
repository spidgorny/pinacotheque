<?php

use Symfony\Component\Process\Process;

class VideoParser
{

	protected $filePath;

	public $log = [];

	public static function fromFile($filePath): VideoParser
	{
		if (!$filePath) {
			throw new InvalidArgumentException(__METHOD__ . ' called with empty $filePath');
		}
		return new static($filePath);
	}

	public function __construct($filePath)
	{
		$this->filePath = $filePath;
	}

	public function log($something)
	{
		$this->log[] = $something;
	}

	public function getMeta()
	{
		$json = $this->probe();
		$creation_time = $json->format->tags->creation_time;
		if ($creation_time) {
			$json->DateTime = str_replace('T', ' ', $creation_time);
		}
		return $json;
	}

	public function saveThumbnailTo($destination)
	{
		$time = '00:00:01.000';
		$probe = $this->probe();
		$this->log('duration' . $probe->format->duration);
		if ($probe->format->duration < 2) {	// 1.045 is not enough
			$time = '00:00:00.000';
		}
		$ffmpeg = getenv('ffmpeg');
		$cmd = [$ffmpeg, '-i', $this->filePath, '-ss', $time, '-vframes', '1', '-vf', 'scale=256:-1', $destination];
		$this->log(implode(' ', $cmd));
		$p = new Process($cmd);
		$ok = $p->run();
		$this->log($p->getErrorOutput());
		$this->log($p->getOutput());
		if ($p->getExitCode()) {
			$error = $p->getErrorOutput();
			debug(implode(' ', $cmd));
			debug($error);
		}
		return !$ok;	// 0 = OK
	}

	public function probe()
	{
		$ffmpeg = getenv('ffmpeg');
		$ffprobe = str_replace('ffmpeg', 'ffprobe', $ffmpeg);
		$cmd = [$ffprobe, '-v', 'quiet', '-print_format', 'json',  '-show_format', '-show_streams', $this->filePath];
		$this->log(implode(' ', $cmd));
		$p = new Process($cmd);
		$p->run();
		return json_decode($p->getOutput(), false, 512, JSON_THROW_ON_ERROR);
	}

}
