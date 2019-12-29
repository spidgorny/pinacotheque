<?php

use nadlib\HTTP\Session;

class Sources extends AppController
{

	/**
	 * @var DBLayerSQLite
	 */
	protected $db;

	protected $prefixURL;

	protected $session;

	/** @var Source  */
	protected $source;

	/**
	 * @var FileProvider
	 */
	protected $provider;

	public function __construct(DBLayerSQLite $db)
	{
		parent::__construct();
		$this->db = $db;
		$this->prefixURL = ShowThumb::class . '?file=';
		$this->session = new Session(__CLASS__);
		$this->source = Source::findByID($this->db, $this->session->get('source', 1));
		$this->provider = new FileProvider($this->db, $this->source);
	}

	public function setSource($id)
	{
		$this->session->set('source', $id);
		$this->request->goBack();
	}

	public function index()
	{
		list('min' => $min, $max => $max) = $this->provider->getMinMax();
		$timelineService = new TimelineServiceForSQL($this->prefixURL);
		$timelineService->min = new Date();
		$timelineService->min->setTimestamp($min);
		$timelineService->max = new Date();
		$timelineService->max->setTimestamp($max);

//		$content[] = 'min: ' . $timelineService->min->format('Y-m-d') . BR;
//		$content[] = 'max: ' . $timelineService->max->format('Y-m-d') . BR;

		$oneByMonth = $this->provider->getOneFilePerMonth();

		$byMonth = $oneByMonth->map(static function (MetaForSQL $meta) {
			$firstMetaRestCount = [$meta];
			$firstMetaRestCount += array_fill(1, $meta->count, null);
			return $firstMetaRestCount;
		});

		$table = $timelineService->renderTable($byMonth);

		$content[] = new slTable($table, [
			'class' => 'table is-fullwidth'
		]);

//		$content[] = getDebug($oneByMonth);

		return $this->template($content, [
			'head' => '<link rel="stylesheet" href="www/css/pina.css" />',
		]);
	}

}
