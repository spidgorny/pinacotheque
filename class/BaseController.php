<?php
/**
 * Created by PhpStorm.
 * User: Slawa
 * Date: 2018-11-18
 * Time: 00:52
 */

class BaseController {

	public function log(...$messages)
	{
		static $stamp;
		if (!$stamp) {
			$stamp = microtime(true);
		}
		$now = microtime(true);
		$since = number_format($now - $stamp, 3);
		echo $since, TAB, implode(TAB, $messages), PHP_EOL;
		$stamp = $now;
	}

}
