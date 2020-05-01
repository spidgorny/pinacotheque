<?php

class TimelineService
{

	var $prefixURL;

	/**
	 * @var string $year -$month
	 */
	public $selected;

	/**
	 * @var Date
	 */
	public $min;

	/**
	 * @var Date
	 */
	public $max;

	public function __construct($prefixURL = null)
	{
		$this->prefixURL = $prefixURL;
	}

	public function groupByDate(array $set)
	{
		$linear = ArrayPlus::create($set);
//		debug($linear->count());

		$byMonth = $linear->reindex(static function ($key, Meta $val) {
			return $val->yearMonth();
		});
//		debug($byMonth->getData());

//		$byDate = $times->reindex(function ($key, $val) {
//			return is_int($key)
//				? date('Y-m-d H:i', $key)
//				: $key;
//		});
		return $byMonth;
	}

	/**
	 * @param array $set
	 * @return array
	 * @deprecated
	 */
	public function groupByYearMonth(array $set)
	{
		$byMonth = $this->groupByDate($set);
		$dates = array_keys($byMonth->getData());
		$dates = array_filter($dates);
//		debug($dates);
//		debug($byDate->countEach());

		$this->min = new Date(min($dates));
		$this->max = new Date(max($dates));

		$table = [];
		for ($year = $this->min->getYear(); $year <= $this->max->getYear(); $year++) {
			$table[$year] = [];
			foreach (range(1, 12) as $month) {
				$key = $year . '-' . $month;
				$images = ifsetor($byMonth[$key], []);
				$table[$year][$month] = $images;
			}
		}
		return $table;
	}

	/**
	 * @param Meta[] $set
	 * @return array
	 */
	public function getTable(array $set)
	{
		$byMonth = $this->groupByDate($set);
		$dates = array_keys($byMonth->getData());
		$dates = array_filter($dates);
//		debug($dates);
//		debug($byDate->countEach());

		$this->min = new Date(min($dates));
		$this->max = new Date(max($dates));

		$table = $this->renderTable($byMonth);
		$table = $this->filterEmptyRows($table);
		return $table;
	}

	/**
	 * Converts 2D array with keys like "1980-01" into
	 * 2D array with keys [1980][01, 02, 03...]
	 * @param array $byMonth
	 * @return array
	 */
	public function renderTable(array $byMonth)
	{
		$table = [];
		for ($year = $this->min->getYear(); $year <= $this->max->getYear(); $year++) {
			$table[$year]['year'] = YearBrowser::makeLink($year);
			$table[$year] += $this->getMonthRow($year, $byMonth);
		}
		return $table;
	}

	/**
	 * @param string $year
	 * @param array $byMonth
	 * @return array
	 */
	public function getMonthRow(string $year, array $byMonth): array
	{
		$row = [];
		$months = range(1, 12);
		foreach ($months as $month) {
			$month = str_pad($month, 2, '0', STR_PAD_LEFT);
			$row[$month] = $this->renderMonth($year, $month, $byMonth);
		}
		return $row;
	}

	public function renderMonth($year, $month, array $byMonth)
	{
		$content = null;
		$key = $year . '-' . $month;
		$images = ifsetor($byMonth[$key], []);
		if ($images) {
//			debug(first($images));
			/** @var Meta $meta */
			$meta = first($images);
			$browser = $this->getMonthBrowserLink($year, $month);
			$selected = $this->selected == $key ? 'active' : '';
			$content = new HTMLTag('td', [
				'class' => $selected
			],
				new htmlString([
					'<figure class="picWithCount">',
					'<a href="' . $browser . '">',
					$meta->toHTML($this->prefixURL, [
						'class' => '',
					]),
					'<div class="count">
							<span class="tag is-info">
  ' . sizeof($images) . '
</span>
</div>',
					'</a>',
					'</figure>'
				]));
		}
		return $content;
	}

	public function getMonthBrowserLink($year, $month)
	{
		return MonthBrowser::href2month($year, $month);
	}

	public function filterEmptyRows(array $table)
	{
		foreach ($table as $key => $row) {
			foreach ($row as $col => $cell) {
				if ($col === 'year') {
					continue;
				}
				if ($cell) {
//					llog($cell);
					continue 2;
				}
			}
			unset($table[$key]);
		}
		return $table;
	}

}
