<?php

class ReadMetaWriteDB extends AppController
{

	/** @var DBInterface */
	protected DBInterface $db;

	public function __construct(DBInterface $db)
	{
		parent::__construct();
		$this->db = $db;
	}

	function __invoke()
	{
		$sourceID = (int)$_SERVER['argv'][2];
		if (!$sourceID) {
			throw new Exception('> php index.php ReadMetaWriteDB <source-id>');
		}
		$source = Source::findByID($this->db, $sourceID);
		if (!$source || !$source->id) {
			throw new Exception('Source ' . $sourceID . ' not found');
		}
		debug($source);

		$provider = new FileProvider($this->db, $source);
		$filesToScan = $provider->getUnscanned();
//		debug(count($filesToScan));

		$profiler = new Profiler();
		foreach ($filesToScan as $i => $fileRow) {
			$file = new MetaForSQL($fileRow);
			$file->injectDB($this->db);
			echo count($filesToScan) - $i, TAB, $file->getFullPath(), ' [', $file->id, ']', PHP_EOL;
			if ($file->hasMeta()) {
				echo 'Has meta', PHP_EOL;
				continue;
			}
			if (!$file->isImage()) {
				continue;
			}
			$ip = ImageParser::fromFile($file->getFullPath());
			$meta = (array)$ip->getMeta();
			if ($meta) {
//				print_r($meta);
				$is = new ImageScanner($file, $this->db);
				$ok = $is->saveMetaToDB($meta);
				$this->log('saveMeta', $ok ? 'OK: ' . count($file->getMetaData()) : '*** FAIL ***');
			} else {
				$this->log('no meta');
			}
		}
		echo 'Done in ', $profiler->elapsed(), PHP_EOL;
	}
}
