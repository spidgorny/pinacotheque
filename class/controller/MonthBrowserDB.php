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

	public function index(/** @noinspection PhpSignatureMismatchDuringInheritanceInspection */ $source, $year, $month)
	{
		$this->source = Source::findByID($this->db, $source);
		$this->year = $year;
		$this->month = $month;

		$this->provider = new FileProvider($this->db, $this->source);
		$data = $this->provider->getFilesForMonth($this->year, $this->month);

		$link2self = MonthBrowserDB::href2month($this->source->id, $this->year, $this->month);
		$timelineService = new TimelineServiceForSQL($link2self);
		$monthSelector = new MonthSelector($this->year, $this->month, $timelineService);
		$content[] = $monthSelector->getMonthSelector($data->getData());

		$this->monthTimeline = new MonthTimeline($this->year, $this->month, ShowThumb::class . '?file=');
		$content[] = $this->monthTimeline->render($data->getData());

		return $this->template($content, [
			'head' => '<link rel="stylesheet" href="www/css/pina.css" />',
		]);
	}

}
