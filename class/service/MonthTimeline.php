<?php

class MonthTimeline
{

	public $data;

	public $year;

	public $month;

	public $prefixURL;

	public function __construct($year, $month, $prefixURL = '')
	{
		$this->year = $year;
		$this->month = $month;
		$this->prefixURL = $prefixURL;
	}

	public function render(array $data)
	{
		$this->data = $data;

		$ms = new MapService();
		$content[] = $ms($data);
		$content[] = '<hr />';

		//debug($this->prefix.'', $this->prefix->getURL().'');
		$sets = $this->splitIntoRows($data);
		if (false) {
			$setSize = array_map(function (array $set) {
				return sizeof($set) . ' : ' . implode(', ', array_map(function ($item) {
						return get_class($item);
					}, $set));
			}, $sets);
			debug($setSize);
		}

		$images = $this->setsToImages($sets);
		$content[] = ['<div class="tile is-ancestor is-vertical">', $images,
			'</div>'
		]; // is-ancestor

		$content = ['<div class="container">', $content, '</div>'];

		$content[] = $this->getTooltipForMeta($data);

		return $content;
	}

	/**
	 * @param Meta[] $data
	 * @return array
	 */
	public function splitIntoRows(array $data): array
	{
		$sets = [];
		$set = [];
		foreach ($data as $i => $meta) {
			$set[] = $meta;

			//$width = $this->getSetWidth($set);
			$width = sizeof($set);
			if ($width >= 4) {
				$sets[] = $set;
				$set = [];
			}
		}
		// add remaining
		if ($set) {
			$sets[] = $set;
		}
		return $sets;
	}

	/**
	 * @param Meta[] $set
	 * @return float|int
	 */
	public function getSetWidth(array $set)
	{
		$width = 0;
		foreach ($set as $meta) {
			$isHorizontal = $meta->isHorizontal();
			$width += $isHorizontal ? 1 : 0.5;
		}
		return $width;
	}

	public function setsToImages(array $sets)
	{
		$content = [];
		$i = 0;
		/** @var Meta[] $set */
		foreach ($sets as $set) {
			$oneWidth = sizeof($set) == 3 ? 'is-4' : 'is-3';
			foreach ($set as &$meta) {
				$img = $meta->toHTML($this->prefixURL, [
					'class' => 'meta',
				]);
				$img->attr('data-index', $i);
				$meta = [
					'<div class="tile is-child ' . $oneWidth . '">',
					$img,
					'</div>',
				];
				$i++;
			}
			$content[] = [
				'<div class="tile is-parent">',
				$set,
				'</div>'
			];
		}
		return $content;
	}

	/**
	 * @param Meta[] $data
	 * @return array
	 */
	public function getTooltipForMeta(array $data): array
	{
		$content = [];
		foreach ($data as $meta) {
			$id = md5($meta->getFilename());
			$someMeta = $meta->getAll();
			unset($someMeta['COMPUTED']);
			foreach ($someMeta as $key => $val) {
				if (!$val) {
					unset($someMeta[$key]);
				}
			}
			$content[] = '<div class="meta4img is-hidden" id="md5-' . $id . '">' . UL::DL($someMeta) . '</div>';
		}
		return $content;
	}

	public function getScripts()
	{
		$items = $this->getOriginalImages($this->data);
		return "<script>
				// build items array
				var items = " . json_encode($items) . ";
			</script>
			<script src='/www/js/photoSwipe.js'></script>
			";
	}

	/**
	 * AJAX?
	 * @param Meta[] $data
	 * @return array
	 */
	public function getOriginalImages(array $data)
	{
		$items = [];
		foreach ($data as $i => $meta) {
			$items[] = [
//				'src' => $meta->getThumbnail($this->prefix->getURL()),
				'src' => $meta->getOriginal(),
				'w' => $meta->width(),
				'h' => $meta->height(),
			];
		}
		return $items;
	}

}
