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
	}

	public function __invoke()
	{
		$set = new MetaSet(getFlySystem($this->prefix));
		$data = $set->filter(function (Meta $meta) {
			return date('Y-m', $meta->FileDateTime)
				== $this->year.'-'.$this->month;
		});
		/** @var Meta[] $data */
		$data = array_values($data);

		$content = ['<div class="tile is-ancestor is-vertical">'];
		$set = [];
		foreach ($data as $i => $meta) {
			$img = $meta->toHTML($this->prefix->getURL());
			$img->attr('data-index', $i);
			$set[] = [
				'<div class="tile is-child is-1">',
				$img,
				'</div>',
			];
			if (($i % 12) == 11) {
				$content[] = [
					'<div class="tile is-parent">',
					$set,
					'</div>'];
				$set = [];
			}
		}
		// add remaining
		if ($set) {
			$content[] = [
				'<div class="tile is-parent">',
				$set,
				'</div>'];
		}
		$content[] = '</div>'; // is-ancestor

		$items = [];
		foreach ($data as $i => $meta) {
			$items[] = [
//				'src' => $meta->getThumbnail($this->prefix->getURL()),
				'src' => $meta->getOriginal(ImgProxy::href([
					'path' => '',
				])),
				'w' => $meta->width(),
				'h' => $meta->height(),
			];
		}

		return $this->template($content, [
			'head' => file_get_contents(__DIR__.'/photoswipe.head.phtml'),
			'foot' => file_get_contents(__DIR__.'/photoswipe.foot.phtml'),
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

}
