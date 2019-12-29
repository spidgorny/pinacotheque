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

	protected $db;

	public static function getTableName()
	{
		return 'files';
	}

	public function __construct(array $meta)
	{
		parent::__construct($meta);
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

}
