<?php

use spidgorny\nadlib\HTTP\URL;

class YearBrowser extends AppController
{

	protected $db;

	protected $prefixURL = 'prefixURL';

	protected $linkPrefix = 'Link prefix';

	public static function makeHref($year)
	{
		return new URL(__CLASS__, [
			'year' => $year,
		]);
	}

	public static function makeLink($year)
	{
		return new HTMLTag('a', [
			'href' => self::makeHref($year),
		], $year);
	}

	public function __construct(DBInterface $db)
	{
		parent::__construct();
		$this->db = $db;
		$this->prefixURL = ShowThumb::href(['file' => '']);
	}

	public function index($year)
	{
		$content[] = '<h3>' . $year . '</h3>';
		for ($month = 1; $month <= 12; $month++) {
			$content[] = $this->renderMonth($year, $month);
		}
		return $content;
	}

	public function renderMonth($year, $month)
	{
		$provider = new FileProvider($this->db);
		$files = $provider->getFilesForMonth($year, $month, false
// 'LIMIT 10'
		);
//		llog($this->db->lastQuery.'');
		$content[] = '<h5>' . date('M', strtotime($year . '-' . $month . '-01')) . ' [' . $files->count() . ']</h5><hr />';

		$this->linkPrefix = Preview::href([
			'year' => $year,
			'month' => $month,
			'file' => ''
		]);

		$first10files = $files->getSlice(0, 10);
		/** @var MetaForSQL $file */
		foreach ($first10files as $file) {
			$content[] = $file->getFullPath();
			$img = $file->toHTMLClickable($this->prefixURL, [
				'class' => 'meta',
			], $this->linkPrefix);
			$meta = [
				'<div class="tile is-child">',
				$img,
				'</div>',
			];
			$content[] = $meta;
		}

		return $content;
	}

}
