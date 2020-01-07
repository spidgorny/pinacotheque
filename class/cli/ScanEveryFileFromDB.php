<?php

class ScanEveryFileFromDB extends AppController
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
		$sourceID = $_SERVER['argv'][2];
		$source = Source::findByID($this->db, $sourceID);
//		debug($source);

		$skipScan = ifsetor($_SERVER['argv'][3]) === '--skipScan';
		if (!$skipScan) {
			$scanDir = new \App\Service\ScanDir($this->db, $source);
			$scanDir();
		}

		$provider = new FileProvider($this->db, $source);
		$filesToScan = $provider->getUnscanned();
//		debug(count($filesToScan));

		/** @var MetaForSQL $fileRow */
		foreach ($filesToScan as $i => $fileRow) {
			$file = new MetaForSQL($fileRow);
			$file->injectDB($this->db);
			echo count($filesToScan) - $i, TAB, $file->getPath(), PHP_EOL;
//			$metaFile = new MetaFile($thumbsPath, $file->getPath());
			$is = new ImageScanner($file, $this->db);
			$is();
		}
	}

}
