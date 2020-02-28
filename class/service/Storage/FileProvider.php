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
			'substr(path, -4)' => new SQLIn([
				'jpeg',
				'.jpg',
				'.png',
				'.gif',
				'.mp4',
				'.mov',
				'.mkv',
				'tiff',
				'.tif',
			]),
			new SQLOr([
				'(meta.value IS NULL)',
				new SQLWhereNotEqual('meta.value', '0000:00:00 00:00:00'),
			]),
		];
		if ($this->source) {
			$where += [
				'source' => $this->source->id,
			];
		}
		list('min' => $min, 'max' => $max) = $this->db->fetchOneSelectQuery(
			'files LEFT OUTER JOIN meta 
			ON (meta.id_file = files.id AND meta.name = "DateTime")', $where, '',
			"min(coalesce(meta.value, $this->strftime)) as min, 
			max(coalesce(meta.value, $this->strftime)) as max");
		llog($this->db->getLastQuery().'');
		return ['min' => $min, 'max' => $max];
	}

	public function getOneFilePerMonth()
	{
		$YM = "CASE WHEN meta.value THEN 
			replace(substr(meta.value, 1, 7), ':', '-')
            ELSE $this->strftimeYM
    END";
		$where = [
			'type' => 'file',
			'substr(path, -4)' => new SQLIn([
				'jpeg',
				'.jpg',
				'.png',
				'.gif',
				'.mp4',
				'.mov',
				'.mkv',
				'tiff',
				'.tif',
			]),
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
		debug($this->db->getLastQuery().'');
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

	public function getFilesForMonth($year, $month): ArrayPlus
	{
		$YM = "CASE WHEN meta.value THEN replace(substr(meta.value, 1, 7), ':', '-')
            ELSE $this->strftimeYM
    	END";
		$where = [
			'type' => 'file',
			'substr(path, -4)' => new SQLIn([
				'jpeg',
				'.jpg',
				'.png',
				'.gif',
				'.mp4',
				'.mov',
				'.mkv',
				'tiff',
				'.tif',
			]),
		];
		if ($this->source) {
			$where += [
				'source' => $this->source->id,
			];
		}
		if ($this->db instanceof DBLayerPDO) {
			$where += [$YM => $year . '-' . $month];
		} else {
			$where += ['YM' => $year . '-' . $month];	// SQLite only column names
		}
		$query = $this->db->getSelectQuery(
			'files LEFT OUTER JOIN meta ON (meta.id_file = files.id AND meta.name = "DateTime")',
			$where,
			$this->db instanceof DBLayerPDO
			? 'ORDER BY ' . $YM
			: 'ORDER BY YM',    // SQLite only column names can be used
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
	 * @return array[]
	 */
	public function getUnscanned()
	{
		$where = [
			'type' => 'file',
			'substr(path, -4)' => new SQLIn([
				'jpeg',
				'.jpg',
				'.png',
				'.gif',
				'.mp4',
				'.mov',
				'.mkv',
				'tiff',
				'.tif',
			]),
			'meta.id' => null,
		];
		if ($this->source) {
			$where += [
				'source' => $this->source->id,
			];
		}
		$res = $this->db->fetchAllSelectQuery('files LEFT OUTER JOIN meta ON (meta.id_file = files.id)', $where, 'ORDER BY timestamp');
//		$content[] = new slTable($imageFiles);
//		$imageFiles = new DatabaseInstanceIterator($this->db, MetaForSQL::class);
//		$imageFiles->setResult($res);
		$imageFiles = $res;
		return $imageFiles;
	}

}
