<?php

/**
 * Class MetaForSQL
 * @property int id
 * @property int source
 * @property string path
 * @property int timestamp
 * @property int colors
 */
class MetaForSQL extends Meta
{

	use DatabaseMixin;
	use DatabaseManipulation;

	/**
	 * @var Source
	 */
	public $sourceInstance;

	public static function getTableName(): string
	{
		return 'files';
	}

	public function __construct(array $meta)
	{
		parent::__construct($meta);
		if ($this->colors) {
			$this->colors = json_decode($this->colors, true, 512,  JSON_THROW_ON_ERROR);
		}
	}

	public function injectDB(DBInterface $db)
	{
		$this->db = $db;
	}

	/**
	 * @param string $prefix 'ShowThumb?file='
	 * @return string
	 */
	public function getThumbnail($prefix = '')
	{
		return $prefix . $this->id;
	}

	public function getThumb()
	{
		return new Thumb($this);
	}

	public function getFullPath()
	{
		$source = $this->getSource();
		$filePath = path_plus($source->path, $this->getPath());
		return $filePath;
	}

	public function getExt()
	{
		return pathinfo($this->path, PATHINFO_EXTENSION);
	}

	public function getSource()
	{
		if ($this->sourceInstance) {
			return $this->sourceInstance;
		}
		$source = Source::findByID($this->db, $this->source);
//		$content[] = getDebug($source);
//		debug($this->db->getLastQuery());
		//debug($this->source, $this->props, $source);
		$this->sourceInstance = $source;
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
			if (strlen($value) && ($value[0] === '{' || $value[0] === '[')) {
				try {
					$try = json_decode($value, $value[0] === '[', 512, JSON_THROW_ON_ERROR);
					if ($try) {
						$value = $try;
					}
				} catch (JsonException $e) {
					// $value = $value
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

	public function toJson()
	{
		$vars = $this->props;
		$vars['thumb'] = $this->getDestination();
		$vars['source_path'] = $this->getSource()->path;
		$vars['meta'] = $this->getMetaData();
		return $vars;
	}

}
