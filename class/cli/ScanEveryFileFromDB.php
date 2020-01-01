<?php

class ScanEveryFileFromDB extends AppController
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
		$sourceID = $_SERVER['argv'][2];
		$source = Source::findByID($this->db, $sourceID);
		$provider = new FileProvider($this->db, $source);
		$filesToScan = $provider->getUnscanned();
//		debug($filesToScan);

		$thumbsPath = path_plus(getenv('DATA_STORAGE'), $source->thumbRoot);

		/** @var MetaForSQL $fileRow */
		foreach ($filesToScan as $fileRow) {
			$file = new MetaForSQL($fileRow);
			echo $file->path, PHP_EOL;
			$metaFile = new MetaFile($thumbsPath, $file->path);
			$is = new ImageScanner($source, $file->path, $thumbsPath, $metaFile, $this->db);
			$is($file->id);
		}
	}

}
