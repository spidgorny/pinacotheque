<?php

class TimelineService
{

	var $prefixURL;

	/**
	 * @var string $year-$month
	 */
	public $selected;

	public function __construct($prefixURL = null)
	{
		$this->prefixURL = $prefixURL;
	}

	/**
	 * @param Meta[] $set
	 * @return array
	 */
	public function getTable(array $set)
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
		$dates = array_keys($byMonth->getData());
		$dates = array_filter($dates);
//		debug($dates);
//		debug($byDate->countEach());

		$min = new Date(min($dates));
		$max = new Date(max($dates));

		$table = [];
		for ($year = $min->getYear(); $year <= $max->getYear(); $year++) {
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
			$row[$month] = '';
			$key = $year . '-' . $month;
			$images = ifsetor($byMonth[$key], []);
			if ($images) {
//					debug(first($images));
				/** @var Meta $meta */
				$meta = first($images);
				$browser = MonthBrowser::href2month($year, $month);
				$selected = $this->selected == $key ? 'active' : '';
				$row[$month] = new HTMLTag('td', [
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
		}
		return $row;
	}

}
