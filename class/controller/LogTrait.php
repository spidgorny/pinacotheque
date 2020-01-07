<?php

trait LogTrait
{

	public function log($class, ...$messages)
	{
		static $stamp;
		if (!$stamp) {
			$stamp = $_SERVER['REQUEST_TIME_FLOAT'];
		}
		$now = microtime(true);
		$since = number_format($now - $stamp, 3);

		if (count($messages) === 1) {
			if (is_scalar($messages[0])) {
				$data = $messages[0];
			} else {
				$data = json_encode($messages[0], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR | JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_SLASHES);
			}
		} else {
			$data = implode(TAB, $messages);
		}

		echo '+', $since, ' ms', TAB, $class, TAB, $data, PHP_EOL;
		$stamp = $now;
	}

}
