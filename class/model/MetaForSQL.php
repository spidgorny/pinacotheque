<?php

/**
 * Class MetaForSQL
 * @property int id
 * @property int source
 * @property string type
 * @property string path
 * @property int timestamp
 * @property int colors
 * @property int width
 * @property int height
 * @property DateTime DateTime
 * @property string ext
 * @property string ym
 * @property string meta_timestamp
 * @property string meta_error
 * @property DateTime mtime
 */
class MetaForSQL extends Meta
{

	use DatabaseMixin;
	use DatabaseManipulation;

	/**
	 * @var Source|null
	 */
	public ?Source $sourceInstance;
	/**
	 * @var array
	 */
	protected $tags = [];

	public static function getTableName(): string
	{
		return 'files';
	}

	public function __construct(array $meta)
	{
		parent::__construct($meta);
		if ($this->colors) {
			$this->colors = json_decode($this->colors, true, 512, JSON_THROW_ON_ERROR);
		}
		if ($this->DateTime) {
			$this->DateTime = new DateTime($this->DateTime);
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
	public function getThumbnailURL($prefix = 'ShowThumb?file=')
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
		if (isset($this->sourceInstance) && !is_null($this->sourceInstance)) {
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
		return date('Y-m', $this->DateTime ?: $this->timestamp);
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
		if (count(array_filter($this->meta))) {
			return $this->meta;
		}
		$this->meta = $this->getMetaAsAssoc();
		return $this->meta;
	}

	public function getMetaAsAssoc(): array
	{
		$assoc = [];
		foreach ($this->getMeta() as $entry) {
			$value = $entry->value;
			if ($value !== '' && ($value[0] === '{' || $value[0] === '[')) {
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
		$vars['thumb'] = $this->isFile() ? $this->getDestination() : null;
		$vars['source_path'] = $this->getSource()->path;
		$vars['meta'] = $this->getMetaData();
		$vars['DateTime'] = $this->DateTime instanceof DateTime
			? $this->DateTime->format(DateTimeInterface::ATOM) : $this->DateTime;
		$vars['width'] = (int)$this->getWidth();
		$vars['height'] = (int)$this->getHeight();
		$vars['tags'] = (array)$this->loadTags();
		return $vars;
	}

	public function ensureMeta()
	{
		if ($this->hasMeta()) {
			return $this->getMeta();
		}
		$processor = ParserFactory::getInstance($this);
		$parser = $processor->getParser();
		$metaData = $parser->getMeta();
//		llog($metaData);
		$is = new ImageScanner($this, $this->db);
		$is->saveMetaToDB($metaData);
		return $metaData;
	}

	public function insertWidthHeight()
	{
		if ($this->width && $this->height) {
			return [$this->width, $this->height];
		}
//		$this->ensureMeta();
		$this->loadMeta();
		$width = $this->getWidth();
		$height = $this->getHeight();
//			echo str_pad($meta->id, 10), TAB, $meta->getExt(), TAB, $width, 'x', $height;
		if ($width && $height) {
			$this->update([
				'width' => $width,
				'height' => $height,
			]);
		}
		return [$width, $height];
	}

	public function isDir()
	{
		return $this->type === 'dir';
	}

	public function loadTags()
	{
		if ($this->tags) {
			return $this->tags;
		}
		$rows = TagModel::findAll($this->db, ['id_file' => $this->id]);
		$tags = array_map(static function (TagModel $row) {
			return $row->tag;
		}, $rows);
		$this->tags = $tags;
		return $tags;
	}

	public function basename()
	{
		return basename($this->path);
	}

	public function dirname()
	{
		return dirname($this->path);
	}

	public function getFolder()
	{
		if ($this->isTypeDir()) {
			$folder = new \Pinacotheque\Model\Folder($this->props);
			$folder->injectDB($this->db);
			$folder->meta = $this->meta;
			$folder->tags = $this->tags;
			assert($folder->path);
			return $folder;
		}

		// else: type = file => search for folder of that file
		$folder = \Pinacotheque\Model\Folder::findOne($this->db, [
			'source' => $this->source,
			'path' => $this->dirname(),
			'type' => 'dir',
		]);

		return $folder;
	}

	public function isTypeFile()
	{
		return $this->type === 'file';
	}

	public function isTypeDir()
	{
		return $this->type === 'dir';
	}

}
