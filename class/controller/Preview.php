<?php

class Preview extends AppController
{

	/** @var DBLayerSQLite */
	protected $db;

	public function __construct(DBLayerSQLite $db)
	{
		parent::__construct();
		$this->db = $db;
	}

	public function __invoke()
	{
		$source = $this->request->getTrimRequired('source');
		$year = $this->request->getTrimRequired('year');
		$month = $this->request->getTrimRequired('month');
		$file = $this->request->getTrimRequired('file');

		$source = Source::findByID($this->db, $source);
		$provider = new FileProvider($this->db, $source);
		$data = $provider->getFilesForMonth($year, $month);
		$monthTimeline = new MonthTimeline($year, $month, ShowThumb::href( ['file' => '']), Preview::href([
			'year' => $year,
			'month' => $month,
			'file' => ''
		]));

		$content = View::getInstance(__DIR__.'/preview.phtml')->render([
			'file1' => $file,
			'images' => json_encode($monthTimeline->getOriginalImages($data->getData())),
		]);
		return $content;
	}

}
