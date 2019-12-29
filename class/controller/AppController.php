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

	/**
	 * @var Request
	 */
	protected $request;

	/** @var Psr\Container\ContainerInterface */
	public $container;

	public function __construct()
	{
		if (!$this->logger) {
			$this->logger = new ErrorLogLogger();
		}
		$this->request = Request::getInstance();
	}

	public function setContainer($container)
	{
		$this->container = $container;
	}

	/**
	 * @return mixed|null
	 * @throws ReflectionException
	 * @throws Exception
	 */
	public function __invoke()
	{
		$action = $this->getValidAction();
		if ($action) {
//			return $this->$action();
			$delegator = new MarshalParams($this);
			return $delegator->call($action);
		}
		return $this->index();
	}

	/**
	 * @return null
	 * @throws Exception
	 */
	public function index()
	{
		throw new Exception('Not implemented');
		/** @noinspection PhpUnreachableStatementInspection */
		return null;
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
			'sources' => $this->getSources(),
        ]);
	}

	public function getSources()
	{
		/** @var DBLayerSQLite $db */
		$db = $this->container->get(DBLayerSQLite::class);
		$sources = Source::findAll($db, [], 'ORDER BY id');
		//debug($sources);
		return $sources;
	}

	public static function href(array $params)
	{
		$plus = '';
		if ($params) {
			$plus = '?'.http_build_query($params);
		}
		return static::class.$plus;
	}

	public function getAction()
	{
		return $this->request->getTrim('action');
	}

	public function getValidAction()
	{
		$action = $this->getAction();
		if (method_exists($this, $action)) {
			return $action;
		}
		return null;
	}

}
