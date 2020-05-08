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
		$this->updateYM();
		$provider = new FileProvider($this->db);
		$filesToScan = $provider->getUnscanned(new DateTime('-2 days'));
//		debug(count($filesToScan));

		$sef = new ScanEveryFileFromDB($this->db);
		$sef->scanUnscanned($filesToScan);
		$this->updateYM();
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
