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

	public static function href($year, $month)
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
		$this->prefix = new Path(
			$this->filesystem->getAdapter()->getPathPrefix()
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
			$set[] = [
				'<div class="tile is-child is-1">',
				$meta->toHTML($this->prefix->getURL()),
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
		$content[] = '</div>'; // is-ancestor
		return $this->template($content);
	}

}
