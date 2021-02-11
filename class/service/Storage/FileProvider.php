<?php

class FileProvider
{

	/**
	 * @var DBInterface
	 */
	protected $db;

	/** @var Source|null */
	protected $source;

	protected $timestamp;

	protected $strftime;

	protected $strftimeYM;
	public $imageExtList = [
			'jpeg',
			'.jpg',
			'.png',
			'.gif',
			'.mp4',
			'.mov',
			'.mkv',
			'tiff',
			'.tif',
		];

	public function __construct(DBInterface $db, Source $source = null)
	{
		$this->db = $db;
		$this->source = $source;
		$this->timestamp = 'datetime(timestamp, "unixepoch")';    // SQLite
		$this->strftime = "strftime(\"%Y:%m:%d %H:%M:%S\", $this->timestamp)";
		$this->strftimeYM = "strftime(\"%Y-%m\", $this->timestamp)";
		if ($this->db instanceof DBLayerPDO) {
			$this->timestamp = 'from_unixtime(timestamp)';
			$this->strftime = "date_format($this->timestamp, '%Y:%m:%d %H:%i:%s')";
			$this->strftimeYM = "date_format($this->timestamp, '%Y-%m')";
		}
	}

	public function getMinMax()
	{
		$where = [
			'type' => 'file',
			'ext' => new SQLIn($this->imageExtList),
			'DateTime' => new SQLWhereNotEqual('DateTime', null),
		];
		if ($this->source) {
			$where += [
				'source' => $this->source->id,
			];
		}
		list('min' => $min, 'max' => $max) = $this->db->fetchOneSelectQuery(
			'files', $where, '',
			"min(DateTime) as min, 
			max(DateTime) as max");
		llog($this->db->getLastQuery() . '');
		return ['min' => $min, 'max' => $max];
	}

	public function getHistogram()
	{
		$where = [
			'type' => 'file',
			'ext' => new SQLIn($this->imageExtList),
			'DateTime' => new SQLWhereNotEqual('DateTime', null),
		];
		if ($this->source) {
			$where += [
				'source' => $this->source->id,
			];
		}
		$data = $this->db->fetchOneSelectQuery(
			'files', $where, 'ORDER BY DateTime',
			"DateTime, count(*) as images");
		llog($this->db->getLastQuery() . '');
		return $data;
	}

	public function getOneFilePerMonth()
	{
		$YM = "CASE 
			WHEN ym THEN ym 
			WHEN meta.value THEN replace(substr(meta.value, 1, 7), ':', '-')
            ELSE $this->strftimeYM
	    END";
		$where = [
			'type' => 'file',
			'ext' => new SQLIn($this->imageExtList),
		];
		if ($this->source) {
			$where += [
				'source' => $this->source->id,
			];
		}
		$imageFiles = $this->db->fetchAllSelectQuery('files LEFT OUTER JOIN meta ON (meta.id_file = files.id AND meta.name = "DateTime")', $where, 'GROUP BY ' . $YM .
			' ORDER BY ' . $YM,
			'min(files.id) as id, 
			' . $YM . ' as YM, 
			count(*) as count'
		);
		debug($this->db->getLastQuery() . '');
//		llog($this->db->getLastQuery() . '');

		//		$content[] = new slTable($imageFiles);
		$imageFiles = ArrayPlus::create($imageFiles);

		$byMonth = $imageFiles->reindex(static function ($key, array $row) {
			return $row['YM'];
		});

		$byMonth = $byMonth->map(function ($el) {
			$row0 = $el[0];
			$meta = new MetaForSQL($row0);
			$meta->injectDB($this->db);
//			debug($meta);
			$firstMetaRestCount = [$meta];
			return $firstMetaRestCount;
		});
		return $byMonth;
	}

	public function getFilesForMonth($year, $month, $isOrder = true, $limit = ''): ArrayPlus
	{
		$YM = "CASE
			WHEN ym THEN ym 
			WHEN meta.value THEN replace(substr(meta.value, 1, 7), ':', '-')
            ELSE $this->strftimeYM
    	END";
		$where = [
			'type' => 'file',
			'ext' => new SQLIn($this->imageExtList),
		];
		if ($this->source) {
			$where += [
				'source' => $this->source->id,
			];
		}

		$zeroMonth = str_pad($month, 2, '0', STR_PAD_LEFT);
		if ($this->db instanceof DBLayerPDO) {
			$where += [$YM => $year . '-' . $zeroMonth];
		} else {
			$where += ['YM' => $year . '-' . $zeroMonth];    // SQLite only column names
		}

		$order = '';
		if ($isOrder) {
			$order =
				$this->db instanceof DBLayerPDO
					? 'ORDER BY ' . $YM
					: 'ORDER BY YM';
		}

		$query = $this->db->getSelectQuery(
			'files LEFT OUTER JOIN meta ON (meta.id_file = files.id AND meta.name = "DateTime")',
			$where,
			$order . ' ' . $limit,    // SQLite only column names can be used
			'meta.*, files.*, ' . $YM . ' as YM'
		);
//		llog($query . '');
		$imageFiles = $this->db->fetchAll($query);
//		llog($this->db->getLastQuery() . '');

//		$content[] = new slTable($imageFiles);
		$imageFiles = ArrayPlus::create($imageFiles);

		$imageFiles = $imageFiles->map(function (array $row) {
			$meta = new MetaForSQL($row);
			$meta->injectDB($this->db);
//			debug($meta);
			return $meta;
		});
		return $imageFiles;
	}

	/**
	 * @param DateTime $olderThan
	 * @return array[]
	 * @noinspection PhpUndefinedClassInspection
	 */
	public function getUnscanned(DateTime $olderThan = null)
	{
		$where = [
			'type' => 'file',
			'ext' => new SQLOr([
				'ext' => null,
				'ext ' => new SQLIn($this->imageExtList)
			]),
			'meta.id' => null,
		];
		if ($this->source) {
			$where += [
				'source' => $this->source->id,
			];
		}
		if ($olderThan) {
			$where += [
				'meta_timestamp' => new SQLOr([
					'meta_timestamp ' => null,
					'meta_timestamp' => new AsIsOp("< '" . $olderThan->format('Y-m-d H:i:s') . "'"),
				]),
			];
		}
		$res = $this->db->fetchAllSelectQuery('files LEFT OUTER JOIN meta ON (meta.id_file = files.id)', $where, 'ORDER BY timestamp');
//		llog($this->db->getLastQuery().'');
//		$content[] = new slTable($imageFiles);
//		$imageFiles = new DatabaseInstanceIterator($this->db, MetaForSQL::class);
//		$imageFiles->setResult($res);
		$imageFiles = $res;
		return $imageFiles;
	}

	public function getAllFiles(array $where = [])
	{
		$where += [
			'type' => 'file',
			'ext' => new SQLOr([
				'ext' => null,
				'ext ' => new SQLIn($this->imageExtList)
			]),
		];
		$res = $this->db->runSelectQuery('files', $where);	// NO ORDER FOR SPEED
		llog($this->db->getLastQuery().'');
		$imageFiles = new DatabaseInstanceIterator($this->db, MetaForSQL::class);
		$imageFiles->setResult($res);
		return $imageFiles;
	}

}
