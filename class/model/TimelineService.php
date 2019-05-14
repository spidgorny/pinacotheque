<?php

class TimelineService
{

	var $prefixURL;

	public function __construct($prefixURL)
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
		$months = range(1, 12);
		for ($year = $min->getYear(); $year <= $max->getYear(); $year++) {
			$table[$year]['year'] = $year;
			foreach ($months as $month) {
				$month = str_pad($month, 2, '0', STR_PAD_LEFT);
				$table[$year][$month] = '';
				$key = $year.'-'.$month;
				$images = ifsetor($byMonth[$key], []);
				if ($images) {
//					debug(first($images));
					/** @var Meta $meta */
					$meta = first($images);
					$browser = MonthBrowser::href2month($year, $month);
					$table[$year][$month] =
						new htmlString([
							'<figure style="position: relative">',
							'<a href="'.$browser.'">',
							$meta->toHTML($this->prefixURL, [
								'class' => '',
							]),
							'</a>',
							'<div class="" 
							style="position: absolute;
							right: 0; bottom: 0">
							<span class="tag is-info">
  '.sizeof($images).'
</span>
</div>',
							'</figure>'
						]);
				}
			}
		}
		return $table;
	}

}
