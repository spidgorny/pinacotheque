<?php

class ScanAll extends AppController
{

	protected DBInterface $db;

	public function __construct(DBInterface $db)
	{
		parent::__construct();
		$this->db = $db;
	}

	public function __invoke()
	{
		$sources = Source::findAll($this->db);

		$ok = 0;
		/** @var Source $s */
		foreach ($sources as $s) {
			$ok += $this->checkSource($s);
		}

		if ($ok !== count($sources)) {
			throw new RuntimeException('Not all sources are online');
		}

		foreach ($sources as $i => $s) {
			echo 'Source #' . $i . ': ', $s->name, PHP_EOL;
			$this->scanFiles($s);
		}
	}

	public function checkSource(Source $source)
	{
		echo $source->name, ' [', $source->path, ']', PHP_EOL;
		$exists = file_exists($source->path);
		echo 'Exists: ', $exists, PHP_EOL;
		return $exists;
	}

	public function scanFiles(Source $source)
	{
		$scanner = new \App\Service\ScanDir($this->db, $source);
		$scanner();
	}

}
