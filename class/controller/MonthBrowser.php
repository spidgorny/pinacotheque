<?php

use League\Flysystem\Filesystem;
use Psr\Container\ContainerInterface;

class MonthBrowser extends AppController
{

	/**
	 * @var Filesystem
	 */
	private $filesystem;

	protected $year;

	protected $month;

	/**
	 * @var Path
	 */
	protected $prefix;

	protected $prefixURL;

	/** @var MetaSet */
	protected $metaSet;

	/**
	 * @var MonthTimeline
	 */
	protected $monthTimeline;

	/**
	 * @var Meta[]
	 */
	protected $linearData;

	public static function href2month($year, $month)
	{
		return __CLASS__ . '/' . $year . '/' . $month;
	}

	public static function route(ContainerInterface $c)
	{
		$request = Request::getInstance();
		$year = $request->getNameless(1);
		$month = $request->getNameless(2);
		$flyThumbs = $c->get('FlyThumbs');
		$ms = $c->get('MetaSet4Thumbs');
		$self = new self($flyThumbs, $ms, $year, $month);
		return $self;
	}

	public function __construct(Filesystem $filesystem, MetaSet $metaSet, $year, $month)
	{
		parent::__construct();
		$this->filesystem = $filesystem;

		if (!$year) {
			throw new InvalidArgumentException('Year: ' . $year);
		}
		$this->year = $year;

		if (!$month) {
			throw new InvalidArgumentException('Month: ' . $month);
		}
		$this->month = $month;

		/** @var \League\Flysystem\Adapter\Local $adapter */
		$adapter = $this->filesystem->getAdapter();
		$this->prefix = new Path(
			$adapter->getPathPrefix()
		);
		$this->prefixURL = substr(
			$this->prefix,
			strlen($_SERVER['DOCUMENT_ROOT']) + 1
		);
		$this->metaSet = $metaSet;

		$this->monthTimeline = new MonthTimeline($this->year, $this->month);
	}

	public function __invoke()
	{
		$data = $this->metaSet->filter(static function (Meta $meta) {
			return $meta->yearMonth()
				=== $this->year . '-' . $this->month;
		});
		/** @var Meta[] $data */
		$this->linearData = array_values($data);

		$action = ifsetor($_REQUEST['action'], 'index');
		return $this->$action();
	}

	/**
	 * @return mixed|string
	 */
	public function index()
	{
		$timelineService = new TimelineService('asd');
		$monthSelector = new MonthSelector($this->year, $this->month, $timelineService);
		$content[] = $monthSelector->getMonthSelector($this->linearData, PhotoTimeline::href());

		$content[] = $this->monthTimeline->render($this->linearData);

		// uses MetaSet again
		$metaFromArray = $this->metaSet->filterMA(function (MetaArray $meta) {
			return $meta->containsYearMonth($this->year, $this->month);
		});
		$content[] = $this->getFoldersInMetaset($metaFromArray);

		return $this->template($content, [
			'head' => file_get_contents(__DIR__ . '/../../template/photoswipe.head.phtml'),
			'foot' => file_get_contents(__DIR__ . '/../../template/photoswipe.foot.phtml'),
			'scripts' => $this->monthTimeline->getScripts(),
		]);
	}

	public function getFoldersInMetaset(array $set)
	{
		//debug($set->get());Í
		$content = [];
		/**
		 * @var string $path
		 * @var MetaArray $info
		 */
		foreach ($set as $path => $info) {
			$info1 = $info->getFirst();
			$content[] = '<li>' . $info1->getPath() . '</li>';
		}
		$content[] = '<hr />';
		return $content;
	}

	/**
	 * @param Meta[] $data
	 * @return false|string
	 */
	public function gps(array $data)
	{
		$ma = new MetaArray($data);
		$places = $ma->getGps();
		return json_encode($places);
	}

}
