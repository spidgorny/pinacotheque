<?php

/**
 * Class PhotoTimeline
 * @see https://www.soas.ac.uk/centenary/timeline/full97626.jpg
 */
class PhotoTimeline extends AppController
{

	protected $prefix = __DIR__.'/../data/thumbs';

	protected $prefixURL;

	public function __construct()
	{
		$this->prefix = realpath($this->prefix);
		$this->prefixURL = substr(
			$this->prefix,
			strlen($_SERVER['DOCUMENT_ROOT'])+1
		);
		$this->prefixURL = str_replace('\\', '/', $this->prefixURL);
//		debug($this->prefix, $_SERVER['DOCUMENT_ROOT'], $this->prefixURL);
	}

	public function __invoke()
	{
		$content = [];
		$set = new MetaSet(getFlySystem($this->prefix));
//		debug($set->size());
//		$times = $set->groupBy('FileDateTime');
		$linear = ArrayPlus::create($set->getLinear());
//		debug($linear->count());

		$byMonth = $linear->reindex(function ($key, $val) {
			$key = ifsetor($val['FileDateTime']);
			return is_int($key)
				? date('Y-m', $key)
				: $key;
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

		$html = new HTML();
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
					$meta = new Meta(first($images));
					$table[$year][$month] =
						new htmlString([
							'<figure style="position: relative">',
							$meta->toHTML($this->prefixURL),
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

		$content[] = new slTable($table, [
			'class' => 'table is-fullwidth'
		]);
		return $this->template($content);
	}

}
