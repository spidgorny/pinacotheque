<?php

/**
 * Class Meta - represents a single file metadata from meta.json
 * @property string _path_
 * @property string FileName
 * @property int FileDateTime
 * @property array COMPUTED
 * @property string GPSLatitudeRef
 * @property array GPSLatitude
 * @property string GPSLongitudeRef
 * @property array GPSLongitude
 * @property mixed DateTimeOriginal
 * @property mixed DateTime
 * @property int id
 */
class Meta implements IMetaData
{

	protected $props = [];

	/** @var string for debugging when used directly (not MetaForSQL) */
	public $sourcePath;

	public function __construct(array $meta)
	{
		$this->props = $meta;
	}

	public function __get($key)
	{
		return ifsetor($this->props[$key]);
	}

	public function __set($key, $val)
	{
		$this->props[$key] = $val;
	}

	public function __isset($key)
	{
		return isset($this->props);
	}

	/**
	 * Filename without path
	 * @return string
	 */
	public function getFilename()
	{
		if (isset($this->FileName)) {
			return $this->FileName;
		}

		if ($this->props['flickr_secret']) {
			$id = $this->props['id'];
			$secret = $this->props['flickr_secret'];
			return $id . '_' . $secret . '.jpg';
		}

		return $this->getPath();
	}

	/**
	 * Get path relative to the root
	 * @return string
	 */
	public function getFullPath()
	{
		return $this->getPath();
	}

	public function getThumbnail($prefix = '')
	{
		$src = $prefix . '/' . $this->_path_ . '/' . $this->getFilename();
		return $src;
	}

	public function getPath()
	{
		return $this->_path_;
	}

	/**
	 * For compatibility.
	 * @see next function getDestination()
	 * @return object
	 */
	public function getSource()
	{
		return (object)[
			'thumbRoot' => $this->sourcePath,
		];
	}

	/**
	 * /data/thumbs/PrefixMerged/folder/path/file.jpg
	 * @return bool|string
	 */
	public function getDestination()
	{
		$absRoot = path_plus(getenv('DATA_STORAGE'), $this->getSource()->thumbRoot);

		$thumbPath = $this->getPath();
		if ($this->isVideo()) {
			$ext = pathinfo($thumbPath, PATHINFO_EXTENSION);
			$thumbPath = str_replace_once('.' . $ext, '.png', $thumbPath);
		}
		$destination = path_plus($absRoot, $thumbPath);

		@mkdir(dirname($destination), 0777, true);
		$real = realpath($destination);    // after mkdir()
		if ($real) {
			$destination = $real;
		}
		return $destination;
	}

	public function toHTML($prefix = '', array $attributes = [])
	{
		$img = HTMLTag::img($this->getThumbnail($prefix), $attributes + [
//			'width' => 256,
				'height' => 256 / 2,
				'style' => [
					'max-height' => '128px',
				],
			]);
		if ($this->isVideo()) {
			$img = HTMLTag::div($img, ['class' => 'video-thumbnail']);
		}
		return $img;
	}

	public function toHTMLClickable($prefix = '', array $attributes = [], $linkPrefix = '', $append = '')
	{
		$img = $this->toHTML($prefix, $attributes) . $append;
		$ahref = HTMLTag::a($linkPrefix . $this->id, $img, [
			'name' => $this->id,
		], true);
		return $ahref;
	}

	/**
	 * Only used in single month view
	 * @see TimelineService->renderMonth()
	 * @param string $prefix
	 * @param array $attributes
	 * @param string $linkPrefix
	 * @return HTMLTag
	 */
	public function toHTMLWithI($prefix = '', array $attributes = [], $linkPrefix = '')
	{
		$span = new HTMLTag('a', [
			'href' => GetMetaInfo::href(['file' => $this->id]),
			'class' => 'tag meta',
			'data-id' => 'md5-' . md5($this->getFilename()),
		], '<i class="fa fa-info"></i>', true);

		$spanI = '<div class="count">'.$span.'</div>';

		$ahref = $this->toHTMLClickable($prefix, $attributes, $linkPrefix, $spanI);
		$content[] = $ahref;

		return new HTMLTag('figure', [
			'class' => 'picWithCount',
		], $content, true);
	}

	public function isImage()
	{
		$ext = pathinfo($this->getPath(), PATHINFO_EXTENSION);
		$ext = strtolower($ext);
		return in_array($ext, [
			'gif', 'jpg', 'jpeg', 'bmp', 'webp', 'tif', 'tiff', 'png'
		]);
	}

	public function isVideo()
	{
		$ext = pathinfo($this->getPath(), PATHINFO_EXTENSION);
		$ext = strtolower($ext);
		return in_array($ext, [
			'mov', 'mp4', 'mpeg', 'mpg', 'avi',
		]);
	}

	public function width()
	{
		return $this->COMPUTED['Width'];
	}

	public function height()
	{
		return $this->COMPUTED['Height'];
	}

	public function getOriginalURL()
	{
		return ImgProxy::href2img($this);
	}

	public function __debugInfo()
	{
		return ['__class__' => get_class($this)] + $this->props;
	}

	protected function gps2Num($coordPart)
	{
		$parts = explode('/', $coordPart);
		if (count($parts) <= 0) {
			return 0;
		}
		if (count($parts) === 1) {
			return $parts[0];
		}
		return (float)$parts[0] / (float)$parts[1];
	}

	/**
	 * @see https://www.codexworld.com/get-geolocation-latitude-longitude-from-image-php/
	 * @return array
	 */
	public function getLocation()
	{
		$GPSLatitudeRef = $this->GPSLatitudeRef;
		$GPSLatitude    = $this->GPSLatitude;
		$GPSLongitudeRef= $this->GPSLongitudeRef;
		$GPSLongitude   = $this->GPSLongitude;

		if (!$GPSLatitude) {
			return null;
		}

//		debug($GPSLatitude, $GPSLatitudeRef, $GPSLongitude, $GPSLongitudeRef);

		$lat_degrees = count($GPSLatitude) > 0
			? $this->gps2Num($GPSLatitude[0]) : 0;
		$lat_minutes = count($GPSLatitude) > 1
			? $this->gps2Num($GPSLatitude[1]) : 0;
		$lat_seconds = count($GPSLatitude) > 2
			? $this->gps2Num($GPSLatitude[2]) : 0;

		$lon_degrees = count($GPSLongitude) > 0
			? $this->gps2Num($GPSLongitude[0]) : 0;
		$lon_minutes = count($GPSLongitude) > 1
			? $this->gps2Num($GPSLongitude[1]) : 0;
		$lon_seconds = count($GPSLongitude) > 2
			? $this->gps2Num($GPSLongitude[2]) : 0;

		$lat_direction = ($GPSLatitudeRef === 'W' or $GPSLatitudeRef === 'S') ? -1 : 1;
		$lon_direction = ($GPSLongitudeRef === 'W' or $GPSLongitudeRef === 'S') ? -1 : 1;

		$latitude = $lat_direction * ($lat_degrees + ($lat_minutes / 60) + ($lat_seconds / (60*60)));
		$longitude = $lon_direction * ($lon_degrees + ($lon_minutes / 60) + ($lon_seconds / (60*60)));

		return array($latitude, $longitude);
	}

	public function getFlickr()
	{
		$farm_id = $this->props['flickr_farm'];
		$server_id = $this->props['flickr_server'];
		$id = $this->props['id'];
		$secret = $this->props['flickr_secret'];
		return "https://farm${farm_id}.staticflickr.com/${server_id}/${id}_${secret}.jpg";
	}

	public function isHorizontal()
	{
		return $this->width() > $this->height();
	}

	public function getAll()
	{
		return $this->props;
	}

	public function yearMonth()
	{
		// 2008:05:08 20:59:49
		$dt = ifsetor($this->props['DateTime']);
		$dto = ifsetor($this->props['DateTimeOriginal']);
		$key = $dt > 0 ? $dt : $dto;
		//debug($dt, $dto, $key);
		if ($key && $key[0] != '0' && str_contains($key, ':')) {
			$parts = trimExplode(':', $key, 3);
			return $parts[0].'-'.$parts[1];
		}
		$key = @$this->FileDateTime;
		return is_int($key)
			? date('Y-m', $key)
			: $key;
	}

	public function getYearMonth()
	{
		return $this->yearMonth();
	}

	public function getSize()
	{
		return ifsetor($this->props['FileSize'], 0);
	}

	public function hasMeta()
	{
		$metaFile = MetaFile::fromPath($this->sourcePath, $this->getPath());
		return $metaFile->has($this->getPath());
	}

	public function __toString()
	{
		return '[Meta: '.json_encode($this->__debugInfo(), JSON_THROW_ON_ERROR).']';
	}

}
