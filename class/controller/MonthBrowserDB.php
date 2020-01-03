<?php

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

	public function init()
	{
		$source = $this->request->getIntRequired('source');
		$this->source = Source::findByID($this->db, $source);
		$this->year = $this->request->getIntRequired('year');
		$this->month = $this->request->getTrimRequired('month');
		$this->month = str_pad($this->month, 2, '0', STR_PAD_LEFT);

		$this->provider = new FileProvider($this->db, $this->source);
	}

	public function index()
	{
		$content = [];
		$scripts = null;
		$this->init();
		$data = $this->provider->getFilesForMonth($this->year, $this->month);
//		debug($data->getData());

		if ($data->count()) {
			//		$link2self = MonthBrowserDB::href2month($this->source->id, $this->year, $this->month);
			$timelineService = new TimelineServiceForSQL(ShowThumb::href(['file' => '']), $this->provider);

			$monthSelector = new MonthSelector($this->year, $this->month, $timelineService);
			$content[] = $monthSelector->getMonthSelector(Sources::href());

			$ms = new MapService();
			$content[] = $ms($data->getData());
			$content[] = '<hr />';

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
			'scripts' => $scripts .
				'<script src="www/js/metaTooltip.js"></script>',
		]);
	}

	/**
	 * AJAX call from mapForMonth.ts
	 * @return false|string
	 */
	public function gps()
	{
		$this->init();
		$data = $this->provider->getFilesForMonth($this->year, $this->month);

		$ma = new MetaArray($data->getData());
		$places = $ma->getGps();
		$places = array_map(static function (Meta $meta) {
			return $meta->getAll();
		}, $places);
		return json_encode($places, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
	}

	/**
	 * AJAX call from mapForMonth.ts
	 */
	public function filterByGPS()
	{
		$bounds = json_decode($this->request->getTrimRequired('bounds'), false, 512, JSON_THROW_ON_ERROR);
		//debug($bounds);

		$southWest = new Geokit\LatLng($bounds->south, $bounds->west);
		$northEast = new Geokit\LatLng($bounds->north, $bounds->east);

		$boundingBox = new Geokit\Bounds($southWest, $northEast);
//		debug([
//			'southwest' => $boundingBox->getSouthWest().'',
//			'northeast' => $boundingBox->getNorthEast().'',
//		]);

		$this->init();
//		debug($this->source, $this->year, $this->month);
		$data = $this->provider->getFilesForMonth($this->year, $this->month);

		$ma = new MetaArray($data->getData());
		$places = $ma->getGps();
//		debug($places);

		// filtering
		$placesInBounds = [];
		foreach ($places as $candidate) {
//			$betweenLat = $this->between($candidate->lat, $bounds->east, $bounds->west);
//			$betweenLon = $this->between($candidate->lon, $bounds->south, $bounds->north);
			$position = new Geokit\LatLng($candidate->lat, $candidate->lon);
			$boolean = $boundingBox->contains($position);
//			echo $position, ' => ', $boolean ? 1 : 0, BR;
			if ($boolean) {
				$placesInBounds[] = $candidate;
			}
		}
//		debug(count($places), count($placesInBounds));

		$this->monthTimeline = new MonthTimeline($this->year, $this->month, ShowThumb::href(['file' => '']), Preview::href([
			'source' => $this->source->id,
			'year' => $this->year,
			'month' => $this->month,
			'file' => ''
		]));
		$content = $this->monthTimeline->render($placesInBounds);

		return $content;
	}

//	public function between($number, $min, $max)
//	{
//		return $number >= $min && $number <= $max;
//	}

}
