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
		list('min' => $min, 'max' => $max) = $this->db->fetchOneSelectQuery(
			'files LEFT OUTER JOIN meta ON (meta.id_file = files.id AND meta.name = "DateTime")', [
			'source' => $this->source->id,
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
				new SQLWhereNotEqual('meta.value', '0000:00:00 00:00:00'),
		], '',
			'min(coalesce(meta.value, strftime("%Y:%m:%d %H:%M:%S", datetime(timestamp, "unixepoch")))) as min, 
			max(coalesce(meta.value, strftime("%Y:%m:%d %H:%M:%S", datetime(timestamp, "unixepoch")))) as max');
        //debug($this->db->getLastQuery().'');
//        $content[] = 'min: ' . $min . BR;
//        $content[] = 'max: ' . $max . BR;
		return ['min' => $min, 'max' => $max];
	}

	public function getOneFilePerMonth()
	{
		$YM = "CASE WHEN meta.value THEN strftime('%Y-%m', replace(substr(meta.value, 0, 11), ':', '-') || substr(meta.value, 11))
            ELSE strftime('%Y-%m', datetime(timestamp, 'unixepoch'))
    END";
		$imageFiles = $this->db->fetchAllSelectQuery('files LEFT OUTER JOIN meta ON (meta.id_file = files.id AND meta.name = "DateTime")', [
			'source' => $this->source->id,
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
		], 'GROUP BY ' . $YM .
			' ORDER BY ' . $YM,
			'meta.*, files.*, ' . $YM . ' as YM, count(*) as count'
		);
		//debug($this->db->getLastQuery().'');

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
		$YM = "CASE WHEN meta.value THEN replace(substr(meta.value, 0, 8), ':', '-')
            ELSE strftime('%Y-%m', datetime(timestamp, 'unixepoch'))
    END";
		$imageFiles = $this->db->fetchAllSelectQuery(
			'files LEFT OUTER JOIN meta ON (meta.id_file = files.id AND meta.name = "DateTime")', [
			'source' => $this->source->id,
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
			'YM' => $year . '-' . $month,
		], 'ORDER BY CASE WHEN meta.value THEN replace(substr(meta.value, 0, 8), \':\', \'-\')
            ELSE strftime(\'%Y-%m\', datetime(timestamp, \'unixepoch\'))
    END',
			'meta.*, files.*, ' . $YM . ' as YM'
		);
//		debug($this->db->getLastQuery().'');

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

	public function getUnscanned()
	{
		$res = $this->db->fetchAllSelectQuery('files LEFT OUTER JOIN meta ON (meta.id_file = files.id)', [
			'source' => $this->source->id,
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
		], 'ORDER BY timestamp');
//		$content[] = new slTable($imageFiles);
//		$imageFiles = new DatabaseInstanceIterator($this->db, MetaForSQL::class);
//		$imageFiles->setResult($res);
		$imageFiles = $res;
		return $imageFiles;
	}

}
