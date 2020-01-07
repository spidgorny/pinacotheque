<?php

use nadlib\HTTP\Session;

class Sources extends AppController
{

	/**
	 * @var DBInterface
	 */
	protected $db;

	protected $prefixURL;

	protected $session;

	/** @var Source */
	protected $source;

	/**
	 * @var FileProvider
	 */
	protected $provider;

	public function __construct(DBInterface $db)
	{
		parent::__construct();
		$this->db = $db;
		$this->prefixURL = ShowThumb::class . '?file=';
		$this->session = new Session(__CLASS__);
		$sourceID = (int)$this->session->get('source', 1);
		$this->source = Source::findByID($this->db, $sourceID);
		$this->provider = new FileProvider($this->db, $this->source);
	}

	public function setSource($id)
	{
		$this->session->set('source', $id);
		$this->request->redirect(self::href());
	}

	public function index()
	{
		$timelineService = new TimelineServiceForSQL($this->prefixURL, $this->provider);
		$table = $timelineService->byMonth->getData();
//		llog(count($table));
//		llog(array_keys($table));
		$table = $timelineService->renderTable($table);
//		llog(count($table));
//		llog(array_keys($table));
		$table = $timelineService->filterEmptyRows($table);
//		llog(count($table));
//		llog(array_keys($table));

		$content[] = new slTable($table, [
			'class' => 'table is-fullwidth'
		]);

//		$content[] = getDebug($oneByMonth);

		return $this->template($content, [
		]);
	}

}
