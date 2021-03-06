<?php

use nadlib\HTTP\Session;

class TimelineServiceForSQL extends TimelineService
{

	/** @var FileProvider */
	protected $provider;

	public $byMonth;

	public function __construct($prefixURL, FileProviderDenormalized $provider)
	{
		parent::__construct($prefixURL);
		$this->provider = $provider;
		['min' => $min, 'max' => $max] = $this->provider->getMinMax();
		$this->min = new Date($min);
		$this->max = new Date($max);

//		$content[] = 'min: ' . $timelineService->min->format('Y-m-d') . BR;
//		$content[] = 'max: ' . $timelineService->max->format('Y-m-d') . BR;

		$oneByMonth = $this->provider->getOneFilePerMonth();

		$this->byMonth = $oneByMonth->map(static function (array $metaList) {
			$meta = first($metaList);
			$metaList += array_fill(1, $meta->count-1, null);
			return $metaList;
		});
		// results in "1980-01" keys
	}

	public function getMonthBrowserLink($year, $month): string
	{
		$session = new Session(Sources::class);
		$source = $session->get('source');
		return MonthBrowserDB::href2month($source, $year, $month);
	}

}
