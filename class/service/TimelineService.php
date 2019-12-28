<?php

class TimelineService
{

	var $prefixURL;

	/**
	 * @var string $year-$month
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

		$byMonth = $linear->reindex(function ($key, Meta $val) {
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

        return $this->renderTable($byMonth);
    }

    public function renderTable($byMonth)
    {
		$table = [];
		for ($year = $this->min->getYear(); $year <= $this->max->getYear(); $year++) {
			$table[$year]['year'] = $year;
			$table[$year] += $this->getMonthRow($year, $byMonth);
		}
		return $table;
	}

	/**
	 * @param string $year
	 * @param ArrayPlus $byMonth
	 * @return array
	 */
	public function getMonthRow(string $year, ArrayPlus $byMonth): array
	{
		$row = [];
		$months = range(1, 12);
		foreach ($months as $month) {
			$month = str_pad($month, 2, '0', STR_PAD_LEFT);
			$row[$month] = $this->renderMonth($year, $month, $byMonth);
		}
		return $row;
	}

	public function renderMonth($year, $month, ArrayPlus $byMonth)
    {
        $content = null;
        $key = $year . '-' . $month;
        $images = ifsetor($byMonth[$key], []);
        if ($images) {
//					debug(first($images));
            /** @var Meta $meta */
            $meta = first($images);
            $browser = MonthBrowser::href2month($year, $month);
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
                    '</a>',
                    '<div class="count">
							<span class="tag is-info">
  ' . sizeof($images) . '
</span>
</div>',
                    '</figure>'
                ]));
        }
        return $content;
    }

}
