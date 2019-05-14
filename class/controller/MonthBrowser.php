<?php

use League\Flysystem\Filesystem;
use Psr\Container\ContainerInterface;

class MonthBrowser extends AppController
{

	/**
	 * @var Filesystem
	 */
	private $filesystem;

	protected $year;

	protected $month;

	/**
	 * @var Path
	 */
	protected $prefix;

	protected $prefixURL;

	/** @var MetaSet */
	protected $metaSet;

	public static function href2month($year, $month)
	{
		return __CLASS__.'/'.$year.'/'.$month;
	}

	public static function route(ContainerInterface $c)
	{
		$request = Request::getInstance();
		$year = $request->getNameless(1);
		$month = $request->getNameless(2);
		$self = new self($c->get('FlyThumbs'), $year, $month);
		return $self;
	}

	public function __construct(Filesystem $filesystem, $year, $month)
	{
		$this->filesystem = $filesystem;
		$this->year = $year;
		$this->month = $month;
		/** @var \League\Flysystem\Adapter\Local $adapter */
		$adapter = $this->filesystem->getAdapter();
		$this->prefix = new Path(
			$adapter->getPathPrefix()
		);
		$this->prefixURL = substr(
			$this->prefix,
			strlen($_SERVER['DOCUMENT_ROOT'])+1
		);
		$this->metaSet = new MetaSet(getFlySystem($this->prefix));
	}

	public function __invoke()
	{
		$data = $this->metaSet->filter(function (Meta $meta) {
			return $meta->yearMonth()
				== $this->year . '-' . $this->month;
		});
		/** @var Meta[] $data */
		$data = array_values($data);

		$action = ifsetor($_REQUEST['action'], 'index');
		return $this->$action($data);
	}

	public function index(array $data)
	{
		$content[] = $this->getMonthSelector($this->metaSet->getLinear());

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

		$items = $this->getOriginalImages($data);
		return $this->template($content, [
			'head' => file_get_contents(__DIR__ . '/../../template/photoswipe.head.phtml'),
			'foot' => file_get_contents(__DIR__ . '/../../template/photoswipe.foot.phtml'),
			'scripts' => "<script>
var pswpElement = document.querySelector('.pswp');

// build items array
var items = ".json_encode($items).";

// define options (if needed)
var options = {
    // optionName: 'option value'
    // for example:
    index: 0 // start at first slide
};

// Initializes and opens PhotoSwipe
Array.prototype.slice.call(document.querySelectorAll('.tile > img'))
.filter(img => {
	img.addEventListener('click', (e) => {
		console.log(e);
		var img = e.target;
		var dataIndex = img.getAttribute('data-index');
		options.index = parseInt(dataIndex, 10);
		console.log(dataIndex, options.index);
		var gallery = new PhotoSwipe( pswpElement, PhotoSwipeUI_Default, items, options);
		gallery.init();
	});
});
</script>"
		]);
	}

	public function getMonthSelector(array $set)
	{
		$timelineService = new TimelineService($this->prefixURL);
		$table = $timelineService->getTable($set);
		$table = [$this->year => $table[$this->year]];
		$slTable = new slTable($table, [
			'class' => 'table is-fullwidth'
		]);
		$slTable->generateThes();
		$slTable->thes['year'] = HTMLTag::a(PhotoTimeline::class, 'Home').'';
		$content[] = $slTable;
		$content[] = '<hr />';
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
				$img = $meta->toHTML($this->prefix->getURL(), [
					'class' => 'meta',
				]);
				$img->attr('data-index', $i);
				$meta = [
					'<div class="tile is-child '.$oneWidth.'">',
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
	 * @param array $data
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

	/**
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

	/**
	 * @param Meta[] $data
	 * @return false|string
	 */
	public function gps(array $data)
	{
		$ma = new MetaArray($data);
		$places = $ma->getGps();
		return json_encode($places);
	}

}
