<?php

class FileProvider
{

	/**
	 * @var DBLayerSQLite
	 */
	protected $db;

	protected $source;

	public function __construct(DBLayerSQLite $db, Source $source)
	{
		$this->db = $db;
		$this->source = $source;
	}

	public function getMinMax()
	{
		list('min' => $min, 'max' => $max) = $this->db->fetchOneSelectQuery('files', [
			'source' => $this->source->id,
			'type' => 'file',
		], '',
			'min(timestamp) as min, max(timestamp) as max');
//        $content[] = 'query: ' . $this->db->getLastQuery() . BR;
//        $content[] = 'min: ' . $min . BR;
//        $content[] = 'max: ' . $max . BR;
		return ['min' => $min, 'max' => $max];
	}

	public function getOneFilePerMonth()
	{
		$YM = "strftime('%Y-%m', datetime(timestamp, 'unixepoch'))";
		$imageFiles = $this->db->fetchAllSelectQuery('files', [
			'source' => $this->source->id,
			'type' => 'file',
		], "GROUP BY " . $YM .
			' ORDER BY ' . $YM,
			'*, ' . $YM . ' as YM, count(*) as count'
		);
//		$content[] = new slTable($imageFiles);
		$imageFiles = ArrayPlus::create($imageFiles);

		$byMonth = $imageFiles->reindex(static function ($key, array $row) {
			return $row['YM'];
		});

		$byMonth = $byMonth->map(static function ($el) {
			$row0 = $el[0];
			$meta = new MetaForSQL($row0);
//			debug($meta);
			$firstMetaRestCount = [$meta];
			return $firstMetaRestCount;
		});
		return $byMonth;
	}

	public function getFilesForMonth($year, $month)
	{
		$YM = "strftime('%Y-%m', datetime(timestamp, 'unixepoch'))";
		$imageFiles = $this->db->fetchAllSelectQuery('files', [
			'source' => $this->source->id,
			'type' => 'file',
			'YM' => $year . '-' . $month,
		], ' ORDER BY timestamp ',
			'*, ' . $YM . ' as YM'
		);
//		$content[] = new slTable($imageFiles);
		$imageFiles = ArrayPlus::create($imageFiles);

		$imageFiles = $imageFiles->map(static function (array $row) {
			$meta = new MetaForSQL($row);
//			debug($meta);
			return $meta;
		});
		return $imageFiles;
	}

	public function getUnscanned()
	{
		$res = $this->db->fetchAllSelectQuery('files LEFT OUTER JOIN meta ON (meta.id_file = files.id)', [
			'source' => $this->source->id,
			'type' => 'file',
			'meta.id' => null,
		], 'ORDER BY timestamp');
//		$content[] = new slTable($imageFiles);
//		$imageFiles = new DatabaseInstanceIterator($this->db, MetaForSQL::class);
//		$imageFiles->setResult($res);
		$imageFiles = $res;
		return $imageFiles;
	}

}
