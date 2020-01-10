<?php

class Preview extends AppController
{

	/** @var DBInterface */
	protected $db;

	public function __construct(DBInterface $db)
	{
		parent::__construct();
		$this->db = $db;
	}

	public function __invoke()
	{
		$source = $this->request->getTrim('source') ?: null;
		$year = $this->request->getTrimRequired('year');
		$month = $this->request->getTrimRequired('month');
		$file = $this->request->getTrimRequired('file');

		if ($source) {
			$source = Source::findByID($this->db, $source);
		}
		$provider = new FileProvider($this->db, $source);
		$data = $provider->getFilesForMonth($year, $month);
		$monthTimeline = new MonthTimeline($year, $month, ShowThumb::href( ['file' => '']), Preview::href([
			'year' => $year,
			'month' => $month,
			'file' => ''
		]));

		$this->request->setCacheable(60 * 60);
		$content = View::getInstance(__DIR__.'/../../../template/preview.phtml')->render([
			'file1' => $file,
			'images' => json_encode($monthTimeline->getOriginalImages($data->getData())),
		]);
		return $content;
	}

}
