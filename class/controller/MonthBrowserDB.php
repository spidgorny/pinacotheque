<?php

use League\Flysystem\Filesystem;

class MonthBrowserDB extends AppController
{

	/** @var DBLayerSQLite */
	protected $db;

	/**
	 * @var Source
	 */
	protected $source;

	protected $year;

	protected $month;

	/**
	 * @var MonthTimeline
	 */
	protected $monthTimeline;

	/** @var FileProvider */
	protected $provider;

	public static function href2month($source, $year, $month)
	{
		return static::href([
			'source' => $source,
			'year' => $year,
			'month' => $month,
		]);
	}

	public function __construct(DBLayerSQLite $db)
	{
		parent::__construct();
		$this->db = $db;
	}

	public function index()
	{
		$content = [];
		$scripts = null;
		$source = $this->request->getIntRequired('source');
		$this->source = Source::findByID($this->db, $source);
		$this->year = $this->request->getIntRequired('year');
		$this->month = $this->request->getTrimRequired('month');

		$this->provider = new FileProvider($this->db, $this->source);
		$data = $this->provider->getFilesForMonth($this->year, $this->month);

		if ($data->count()) {
			//		$link2self = MonthBrowserDB::href2month($this->source->id, $this->year, $this->month);
			$timelineService = new TimelineServiceForSQL(ShowThumb::href(['file' => '']));
			$monthSelector = new MonthSelector($this->year, $this->month, $timelineService);
			$content[] = $monthSelector->getMonthSelector($data->getData(), Sources::href());

			$this->monthTimeline = new MonthTimeline($this->year, $this->month, ShowThumb::href(['file' => '']), Preview::href([
				'source' => $this->source->id,
				'year' => $this->year,
				'month' => $this->month,
				'file' => ''
			]));
			$content[] = $this->monthTimeline->render($data->getData());

			$scripts = $this->monthTimeline->getScripts();
		}

		return $this->template($content, [
			'head' => '',
//				. file_get_contents(__DIR__ . '/../../template/photoswipe.head.phtml'),
//			'foot' => file_get_contents(__DIR__ . '/../../template/photoswipe.foot.phtml'),
			'scripts' => $scripts,
		]);
	}

}
