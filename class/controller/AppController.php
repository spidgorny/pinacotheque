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

	/**
	 * @Inject
	 * @var ErrorLogLogger
	 */
	protected $logger;

	public function __construct()
	{
		if (!$this->logger) {
			$this->logger = new ErrorLogLogger();
		}
	}

	public function log($key, ...$data)
	{
		$caller = Debug::getCaller();
		if ($key && $data) {
			$this->logger->log($caller, [$key => $data]);
		} else {
			$this->logger->log($caller, $key);
		}
	}

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
