<?php

/**
 * Class InitDB
 * Querying meta data every time is slow.
 * We need to denormalize the data and store DateTime in 'files'
 */
class InitDB extends AppController
{

	use LogTrait;

	/**
	 * @var DBInterface
	 */
	protected $db;

	public function __construct(DBInterface $pdo)
	{
		parent::__construct();
		$this->db = $pdo;
	}

	function __invoke()
	{
//		$res = $this->db->runSelectQuery('SELECT * FROM file WHERE DateTime is NULL');
		$iterator = new DatabaseInstanceIterator($this->db, MetaForSQL::class);
		$iterator->perform("
		SELECT * FROM files 
		WHERE type = 'file' 
		AND DateTime is NULL");
		$amount = $iterator->count();
		/** @var MetaForSQL $meta */
		foreach ($iterator as $i => $meta) {
			$meta->injectDB($this->db);
			$metaData = $meta->getMetaData();
			if (isset($metaData['DateTime'])) {
				$meta->update([
					'DateTime' => $metaData['DateTime'],
				]);
				$this->log($amount - $i, '✔', $meta->getFullPath());
			} else {
				$this->log($amount - $i, '✕', $meta->getFullPath());
			}
		}
	}

}
