<?php

class SetCorrectDateTimeNotTest extends AppController
{
	const YMDHis = 'Y-m-d H:i:s';

	/**
	 * @var DBInterface
	 */
	protected $db;

	protected $count;

	public function __construct(DBInterface $db)
	{
		parent::__construct();
		$this->db = $db;
	}

	public function index()
	{
		$this->processAllUnprocessed();
	}

	public function processAllUnprocessed()
	{
		$provider = new FileProvider($this->db);
		$query = SQLSelectQuery::getSelectQueryP($this->db, 'files', [
			'type' => 'file',
			'ext' => new SQLIn($provider->imageExtList),
			'mtime' => null,
		], 'ORDER BY id');
		$feed = new DatabaseResultIteratorAssoc($this->db);
		$feed->perform($query);
		$this->count = $feed->count();
		foreach ($feed as $row) {
			$this->processOne($row);
		}
	}

	/**
	 * @param $row
	 */
	public function processOne($row): void
	{
		$file = new MetaForSQL($row);
		$file->injectDB($this->db);
		try {
			$meta = $file->getMetaData();
			$dateTime = $this->getDateTime($file->getOriginal(), $meta, $file->timestamp);
			echo $this->count--, TAB, ifsetor($meta['DateTime']), ' => ', $dateTime, PHP_EOL;
			$file->update([
				'mtime' => new SQLNow(),
				'DateTime' => $dateTime,
			]);
		} catch (RuntimeException $e) {
			debug($file, $file->getOriginal());
			throw $e;
		}
	}

	private function getDateTime(string $filename, array $meta, int $timestamp)
	{
		if (count($meta) === 0) {
			// there is nothing to parse
			return date(self::YMDHis, $timestamp);
		}

		$DateTime = $meta['DateTime'] ?? null;
		if ($DateTime) {
			return $this->fromDateTime($DateTime);
		}

		$DateTimeOriginal = $meta['DateTimeOriginal'] ?? null;
		if ($DateTimeOriginal) {
			return $this->fromDateTime($DateTimeOriginal);
		}


		$DateTimeDigitized = $meta['DateTimeDigitized'] ?? null;
		if ($DateTimeDigitized) {
			return $this->fromDateTime($DateTimeDigitized);
		}

		$FileDateTime = $meta['FileDateTime'] ?? null;
		if ($FileDateTime && count($meta) <= 13) {	// last resort for a specific limited file
			return $this->fromDateTime($FileDateTime);
		}

		$creation_time = $meta['format']->tags->creation_time ?? null;
		if ($creation_time) {
			return $this->fromDateTime($creation_time);
		}

		$ymdhis = '/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/';
		if (preg_match($ymdhis, $filename, $matches)) {
			$formatted = $matches[1].'-'.$matches[2].'-'.$matches[3].' '.$matches[4].':'.$matches[5].':'.$matches[6];
			return $this->fromDateTime($formatted);
		}

		debug($timestamp, date(self::YMDHis, $timestamp), $meta);
		throw new RuntimeException('Implement parsing of this meta');
	}

	private function fromDateTime($input)
	{
		$withSemi = '/^\d{4}:\d{2}:\d{2}/';
		if (preg_match($withSemi, $input)) {
			$parts = trimExplode(' ', $input);
			$parts[0] = str_replace(':', '-', $parts[0]);
			$result = implode(' ', $parts);
			if (false === strtotime($result)) {
				throw new RuntimeException('Unable to parse ' . $input);
			}
			return $result;
		}

		$withDash = '/^\d{4}-\d{2}-\d{2}/';
		if (preg_match($withDash, $input)) {
			$result = substr($input, 0, 19);    // without milliseconds
			if (false === strtotime($result)) {
				throw new RuntimeException('Unable to parse ' . $input);
			}
			return $result;
		}

		if (is_numeric($input) && strlen($input) >= 10) {
			$int = (int)substr($input, 0, 10);
			if ($int > time()) {
				throw new RuntimeException('Unable to parse ' . $input);
			}
			return date(self::YMDHis, $int);
		}

		throw new RuntimeException('Unable to parse ' . $input);
	}

}
