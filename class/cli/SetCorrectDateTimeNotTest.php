<?php

class SetCorrectDateTimeNotTest extends AppController
{
	const YMDHis = 'Y-m-d H:i:s';

	/**
	 * @var DBInterface
	 */
	protected DBInterface $db;

	protected int $remaining;

	protected int $processed = 0;

	protected float $startTime;

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
		$this->remaining = (int)$feed->count();
		$this->startTime = microtime(true);
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
			$meta = $file->loadMeta();
			$dateTime = $this->getDateTime($file->getOriginal(), $meta, $file->timestamp);
			$speed = $this->processed++ / (microtime(true) - $this->startTime);
			echo $this->remaining--, TAB, number_format($speed, 3), '/s', TAB, ifsetor($meta['DateTime']), ' => ', $dateTime, PHP_EOL;
			$update = [
				'mtime' => new SQLNow(),
				'DateTime' => $dateTime,
			];
			if (!$file->width) {
				$update += [
					'width' => $file->getWidth(),
					'height' => $file->getHeight(),
				];
			}
			$file->update($update);
		} catch (RuntimeException $e) {
			//debug($file, $file->getOriginal());
			//throw $e;
		}
	}

	public function getDateTime(string $filename, array $meta, int $timestamp)
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
		if ($FileDateTime && count($meta) <= 15) {    // last resort for a specific limited file
			return $this->fromDateTime($FileDateTime);
		}

		$creation_time = $meta['format']->tags->creation_time ?? null;
		if ($creation_time) {
			return $this->fromDateTime($creation_time);
		}

		$isTelegram = str_contains($filename, 'Telegram Video');
		$ymdhis = '/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/';
		$match = preg_match($ymdhis, basename($filename), $matches);
		if (!$isTelegram && $match && in_array($matches[1][1], [1, 2], false)) {
			$formatted = $matches[1] . '-' . $matches[2] . '-' . $matches[3] . ' ' . $matches[4] . ':' . $matches[5] . ':' . $matches[6];
			return $this->fromDateTime($formatted);
		}

		$ymd_his = '/(\d{4})(\d{2})(\d{2})_(\d{2})(\d{2})(\d{2})/';
		$match = preg_match($ymd_his, basename($filename), $matches);
		llog($match);
		if ($match && in_array($matches[1][1], [1, 2], false)) {
			debug($filename, $isTelegram, $match);
			$formatted = $matches[1] . '-' . $matches[2] . '-' . $matches[3] . ' ' . $matches[4] . ':' . $matches[5] . ':' . $matches[6];
			return $this->fromDateTime($formatted);
		}

		$ymd = '/VID-(\d{4})(\d{2})(\d{2})-WA/';    // what's app
		if (preg_match($ymd, basename($filename), $matches) && in_array($matches[1][1], [1, 2], false)) {
			$formatted = $matches[1] . '-' . $matches[2] . '-' . $matches[3];
			return $this->fromDateTime($formatted);
		}

		if ($isTelegram) {    // there is no hope to find any metadata
			return date(self::YMDHis, $timestamp);
		}

		$isIPad = str_contains($filename, 'iPad2018');
		$isVideoshow = str_contains($filename, '1Videoshow');
		if ($isIPad || $isVideoshow) {
			return date(self::YMDHis, $timestamp);
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
