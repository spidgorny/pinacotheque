<?php

class FileProviderDenormalized
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

	protected $DateTimeAsDate;

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
			$this->DateTimeAsDate = "STR_TO_DATE(DateTime, '%Y:%m,%d %H:%i:%s')";
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
			// this does not allow to see photos without metadata
			new SQLOr([
				'(DateTime IS NULL)',
				new AsIsOp('DateTime > 0'),
			]),
		];
		if ($this->source) {
			$where += [
				'source' => $this->source->id,
			];
		}
		list('min' => $min, 'max' => $max) = $this->db->fetchOneSelectQuery(
			'files', $where, '',
			"min(coalesce($this->timestamp, $this->DateTimeAsDate)) as min, 
						max(coalesce($this->timestamp, $this->DateTimeAsDate)) as max");
//		debug($this->db->getLastQuery().'');
//        $content[] = 'min: ' . $min . BR;
//        $content[] = 'max: ' . $max . BR;
//		llog($this->db->getLastQuery());
		return ['min' => $min, 'max' => $max];
	}

	public function getOneFilePerMonth()
	{
		$YM = "CASE WHEN DateTime THEN 
			replace(substr(DateTime, 1, 7), ':', '-')
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
		$imageFiles = $this->db->fetchAllSelectQuery('files', $where, 'GROUP BY ' . $YM .
			' ORDER BY ' . $YM,
			'min(files.id) as id, 
			' . $YM . ' as YM, 
			count(*) as count'
		);
		//debug($this->db->getLastQuery().'');
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
		$YM = "CASE WHEN DateTime THEN replace(substr(DateTime, 1, 7), ':', '-')
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
			'files',
			$where,
			$this->db instanceof DBLayerPDO
			? 'ORDER BY ' . $YM
			: 'ORDER BY YM',    // SQLite only column names can be used
			'files.*, ' . $YM . ' as YM'
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

}
