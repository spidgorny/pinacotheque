<?php

class MonthBrowserDB extends AppController
{

	/** @var DBInterface */
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

	public function __construct(DBInterface $db)
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
		$images = $this->provider->getFilesForMonth($this->year, $this->month)->getData();
//		debug($images->getData());

		$bounds = $this->request->getTrim('bounds');
		if ($bounds) {
			// don't filter, if bounds unspecified, because it will hide all images without GPS
			$images = $this->filterByBounds($images);
		}

		if (count($images)) {
			//		$link2self = MonthBrowserDB::href2month($this->source->id, $this->year, $this->month);
			$timelineService = new TimelineServiceForSQL(ShowThumb::href(['file' => '']), $this->provider);

			$monthSelector = new MonthSelector($this->year, $this->month, $timelineService);
			$content[] = $monthSelector->getMonthSelector(Sources::href());

			$ms = new MapService();
			$content[] = $ms($images);
			$content[] = '<hr />';

			$this->monthTimeline = new MonthTimeline($this->year, $this->month, ShowThumb::href(['file' => '']), Preview::href([
				'source' => $this->source->id,
				'year' => $this->year,
				'month' => $this->month,
				'file' => ''
			]));
			$content[] = $this->monthTimeline->render($images);

//			$scripts = $this->monthTimeline->getScripts();
		}

		$this->request->setCacheable(60 * 60);
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
		$images = $this->provider->getFilesForMonth($this->year, $this->month);

		$placesInBounds = $this->filterByBounds($images->getData());
//		debug(count($images), count($placesInBounds));

		$places = array_map(static function (Meta $meta) {
			return $meta->getAll();
		}, $placesInBounds);
		return json_encode($places, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
	}

	public function filterByBounds(array $places)
	{
		// filter only images with GPS info first, before return below
		$ma = new MetaArray($places);
		$places = $ma->getGps();	// only images with GPS
//		debug($places);

		$bounds = $this->request->getTrim('bounds');
		if (!$bounds) {
			return $places;	// return everything
		}
		$bounds = json_decode($bounds, false, 512, JSON_THROW_ON_ERROR);
//		debug($bounds);

		$southWest = new Geokit\LatLng($bounds->south, $bounds->west);
		$northEast = new Geokit\LatLng($bounds->north, $bounds->east);

		$boundingBox = new Geokit\Bounds($southWest, $northEast);
//		debug([
//			'southwest' => $boundingBox->getSouthWest().'',
//			'northeast' => $boundingBox->getNorthEast().'',
//		]);

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
		return $placesInBounds;
	}

	/**
	 * AJAX call from mapForMonth.ts
	 */
	public function filterByGPS()
	{
		$this->init();
//		debug($this->source, $this->year, $this->month);
		$images = $this->provider->getFilesForMonth($this->year, $this->month);

		$placesInBounds = $this->filterByBounds($images->getData());

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
