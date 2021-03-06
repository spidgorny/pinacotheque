<?php

use nadlib\HTTP\Session;

class AppController
{

	use LogTrait;

	protected array $validImages = [
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
	protected ErrorLogLogger $logger;

	/**
	 * @var Request
	 */
	protected Request $request;

	/** @var Psr\Container\ContainerInterface */
	public \Psr\Container\ContainerInterface $container;

	public string $defaultAction = 'index';

	public function __construct()
	{
		$this->logger = new ErrorLogLogger();
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
		$action = $this->getValidAction() ?: $this->defaultAction;
//		return $this->$action();
		$delegator = new MarshalParams($this);
		return $delegator->call($action);
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

	function template($body, array $params = [], $templateFile = __DIR__ . '/../../template/template.phtml')
	{
		$view = View::getInstance($templateFile, $this);
		$base = new Path(getcwd());	// CWD != __DIR__
//		$base = $base->up()->up();
		$view->baseHref = cap($base->getURL());
//		debug($base.'', $base->getURL().'');

		$s = new Session(Sources::class);
		$iSource = (int)$s->get('source', 1);

		return $view->render($params + [
			'head' => '',
			'body' => MergedContent::mergeStringArrayRecursive($body),
			'foot' => '',
			'scripts' => '',
			'sources' => $this->getSources(),
			'iSource' => $iSource,
        ]);
	}

	public function getSources()
	{
		/** @var DBInterface $db */
		$db = $this->container->get(DBInterface::class);
		$sources = Source::findAll($db, [], 'ORDER BY id');
//		debug($sources);
		return $sources;
	}

	public static function href(array $params = [])
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
