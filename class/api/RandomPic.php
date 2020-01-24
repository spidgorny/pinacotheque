<?php

class RandomPic extends AppController
{

	/** @var DBInterface */
	protected $db;

	public function __construct(DBInterface $db)
	{
		parent::__construct();
		$this->db = $db;
	}

	public function index()
	{
		$start = microtime(true);
		$row = $this->db->fetchOneSelectQuery('files', [
			'type' => 'file',
		], 'ORDER BY rand() LIMIT 1');
		$file = new MetaForSQL($row);
		$file->injectDB($this->db);
		$row['thumb'] = $file->getDestination();
		$row['url'] = $file->getFullPath();
		$http = 'http://' . $this->request->getHost();
		$row['preview'] = $http . '/ShowThumb?file=' . $file->id;
		$row['original'] = $http . '/ShowOriginal?file=' . $file->id;
		$row['duration'] = microtime(true) - $start;
		$this->request->json($row);
	}

}
