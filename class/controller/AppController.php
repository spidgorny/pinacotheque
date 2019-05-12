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
		$view->baseHref = (new Path(getcwd()))->getURL();
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
