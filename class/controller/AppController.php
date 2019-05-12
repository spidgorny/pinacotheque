<?php

class AppController
{

	protected $validImages = [
		'jpg',
		'git',
		'png',
		'jpeg',
		'tif',
		'tiff',
	];

	function template($body, array $params = [])
	{
		$view = View::getInstance(__DIR__ . '/../../template/template.phtml', $this);
		$base = new Path(getcwd());	// CWD != __DIR__
//		$base = $base->up()->up();
		$view->baseHref = cap($base->getURL());
//		debug($base.'', $base->getURL().'');
		return $view->render($params + [
			'head' => '',
			'body' => $body,
			'foot' => '',
			'scripts' => '',
        ]);
	}

	public static function href(array $params)
	{
		$plus = '';
		if ($params) {
			$plus = '?'.http_build_query($params);
		}
		return static::class.$plus;
	}

}
