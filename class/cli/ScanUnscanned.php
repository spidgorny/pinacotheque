<?php

class ScanUnscanned extends AppController
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
		$provider = new FileProvider($this->db);
		$filesToScan = $provider->getUnscanned();
//		debug(count($filesToScan));

		$sef = new ScanEveryFileFromDB($this->db);
		$sef->scanUnscanned($filesToScan);
	}

	public function updateYM()
	{
		$query = "UPDATE files
JOIN meta ON (meta.id_file = files.id AND meta.name = 'DateTime')
SET files.ym = date_format(replace(substr(meta.value, 1, 7), ':', '-'), '%Y-%m')
WHERE NOT files.ym";
		$res = $this->db->perform($query);
		echo 'Update YM: ', $this->db->affectedRows($res), PHP_EOL;
	}

}
