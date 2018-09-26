<?php

class AppController
{

	function template($body, array $params = [])
	{
		$view = View::getInstance(__DIR__ . '/template.phtml', $this);
		return $view->render(['body' => $body] + $params);
	}

}
