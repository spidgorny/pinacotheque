<?php

class MonthSelector
{

	protected $year;

	protected $month;

	/**
	 * @var TimelineServiceForSQL
	 */
	protected $timelineService;

	public function __construct($year, $month, $timelineService)
	{
		$this->year = $year;
		$this->month = $month;
		$this->timelineService = $timelineService;
	}

	public function getMonthSelector($linkHome)
	{
		$this->timelineService->selected = $this->year . '-' . $this->month;
		$table = $this->timelineService->renderTable($this->timelineService->byMonth->getData());
//		debug(count($set), array_keys($table));
		$table = [$this->year => $table[$this->year]];
		$slTable = new slTable($table, [
			'class' => 'table is-fullwidth'
		]);
		$slTable->generateThes();
		$slTable->thes['year'] = HTMLTag::a($linkHome, 'Home') . '';
		$content[] = $slTable;
		$content[] = '<hr />';
		return $content;
	}


}
