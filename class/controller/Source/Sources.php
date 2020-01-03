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

	/** @var Source  */
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
		$this->source = Source::findByID($this->db, $this->session->get('source', 1));
		$this->provider = new FileProvider($this->db, $this->source);
	}

	public function setSource($id)
	{
		$this->session->set('source', $id);
		$this->request->redirect(Sources::href());
	}

	public function index()
	{
		$timelineService = new TimelineServiceForSQL($this->prefixURL, $this->provider);
		$table = $timelineService->renderTable($timelineService->byMonth);

		$content[] = new slTable($table, [
			'class' => 'table is-fullwidth'
		]);

//		$content[] = getDebug($oneByMonth);

		return $this->template($content, [
		]);
	}

}
