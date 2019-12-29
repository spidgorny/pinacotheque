<?php

use League\Flysystem\Filesystem;

class MonthBrowserDB extends AppController
{

	/** @var DBLayerSQLite */
	protected $db;

	/**
	 * @var Source
	 */
	protected $source;

	protected $year;

	protected $month;

	public static function href2month($source, $year, $month)
	{
		return static::href([
			'source' => $source,
			'year' => $year,
			'month' => $month,
		]);
	}

	public function __construct(DBLayerSQLite $db)
	{
		$this->db = $db;
		parent::__construct();
	}

	public function index(/** @noinspection PhpSignatureMismatchDuringInheritanceInspection */ $source, $year, $month)
	{
		$this->source = Source::findByID($this->db, $source);
		$this->year = $year;
		$this->month = $month;
		return $this->template('asd' . $year . '-' . $month);
	}

}
