<?php
/**
 * Created by PhpStorm.
 * User: Slawa
 * Date: 2018-11-18
 * Time: 00:52
 */

class BaseController extends AppController
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
				$data = json_encode($messages[0], JSON_THROW_ON_ERROR);
			}
		} else {
			$data = implode(TAB, $messages);
		}

		echo '+', $since, ' ms', TAB, $class, TAB, $data, PHP_EOL;
		$stamp = $now;
	}

}
