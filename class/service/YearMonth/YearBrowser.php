<?php

use spidgorny\nadlib\HTTP\URL;

class YearBrowser extends AppController
{

	protected DBInterface $db;

	protected string $prefixURL = 'prefixURL';

	protected string $linkPrefix = 'Link prefix';

	public string $defaultAction = 'indexYear';

	public static function makeHref($year): URL
	{
		return new URL(__CLASS__, [
			'year' => $year,
		]);
	}

	public static function makeLink($year): HTMLTag
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

	public function indexYear($year): array
	{
		$content[] = '<h3>' . $year . '</h3>';
		for ($month = 1; $month <= 12; $month++) {
			$content[] = $this->renderMonth($year, $month);
		}
		return $content;
	}

	public function renderMonth($year, $month): array
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
