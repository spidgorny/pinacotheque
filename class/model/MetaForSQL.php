<?php

/**
 * Class MetaForSQL
 * @property int id
 * @property int source
 * @property string path
 * @property int timestamp
 */
class MetaForSQL extends Meta
{

	use DatabaseMixin;
	use DatabaseManipulation;

	public static function getTableName()
	{
		return 'files';
	}

	public function __construct(array $meta)
	{
		parent::__construct($meta);
	}

	public function injectDB(DBInterface $db)
	{
		$this->db = $db;
	}

	public function getThumbnail($prefix = '')
	{
		return $prefix . $this->id;
	}

	public function getFullPath()
	{
		$source = $this->getSource();
		$filePath = path_plus($source->path, $this->getPath());
		return $filePath;
	}

	public function getSource()
	{
		$source = Source::findByID($this->db, $this->source);
//		$content[] = getDebug($source);
//		debug($this->db->getLastQuery());
		//debug($this->source, $this->props, $source);
		return $source;
	}

	/**
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}

	public function getFilename()
	{
		return basename($this->getPath());
	}

	public function yearMonth()
	{
		return date('Y-m', $this->timestamp);
	}

	public function getOriginal()
	{
		return $this->getFullPath();
	}

	public function getOriginalURL()
	{
		return ShowOriginal::href(['file' => $this->id]);
	}

	/**
	 * @return MetaEntry[]
	 */
	public function getMeta(): array
	{
		$metaRows = MetaEntry::findAll($this->db, [
			'id_file' => $this->id,
		]);
		return $metaRows;
	}

	public function getMetaData(): array
	{
		$assoc = [];
		foreach ($this->getMeta() as $entry) {
			$value = $entry->value;
			if ($value[0] === '{' || $value[0] === '[') {
				$try = json_decode($value, $value[0] === '[', 512, JSON_THROW_ON_ERROR);
				if ($try) {
					$value = $try;
				}
			}
			$assoc[$entry->name] = $value;
		}
		return $assoc;
	}

	public function getLocation()
	{
		$metaData = (object)$this->getMetaData();
		$this->GPSLatitudeRef = ifsetor($metaData->GPSLatitudeRef);
		$this->GPSLatitude = ifsetor($metaData->GPSLatitude);
		$this->GPSLongitudeRef = ifsetor($metaData->GPSLongitudeRef);
		$this->GPSLongitude = ifsetor($metaData->GPSLongitude);
		return parent::getLocation();
	}

	public function hasMeta()
	{
		$metaRows = $this->getMeta();
		return count($metaRows);
	}

	/**
	 * /data/thumbs/PrefixMerged/folder/path/file.jpg
	 * @return bool|string
	 */
	public function getDestination()
	{
		$absRoot = path_plus(getenv('DATA_STORAGE'), $this->getSource()->thumbRoot);
		$destination = path_plus($absRoot, $this->getPath());
		@mkdir(dirname($destination), 0777, true);
		$real = realpath($destination);    // after mkdir()
		if ($real) {
			$destination = $real;
		}
		return $destination;
	}

}
