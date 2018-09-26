<?php

use League\Flysystem\Filesystem;

/**
 * Class PhotoTimeline
 * @see https://www.soas.ac.uk/centenary/timeline/full97626.jpg
 */
class PhotoTimeline extends AppController
{

	protected $fileSystem;

	protected $prefix;

	protected $prefixURL;

	public function __construct(Filesystem $fileSystem)
	{
		$this->fileSystem = $fileSystem;
		$this->prefix = $fileSystem->getAdapter()->getPathPrefix();
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
			$key = @$val->FileDateTime;
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
					$meta = first($images);
					$browser = MonthBrowser::href($year, $month);
					$table[$year][$month] =
						new htmlString([
							'<figure style="position: relative">',
							'<a href="'.$browser.'">',
							$meta->toHTML($this->prefixURL),
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

		$content[] = new slTable($table, [
			'class' => 'table is-fullwidth'
		]);
		return $this->template($content);
	}

}
