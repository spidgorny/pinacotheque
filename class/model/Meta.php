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
 */
class Meta
{

	protected $props = [];

	public function __construct(array $meta)
	{
		$this->props = $meta;
	}

	public function __get($key)
	{
		return ifsetor($this->props[$key]);
	}

	public function getFilename()
	{
		return $this->props['FileName'];
	}

	public function getThumbnail($prefix = '')
	{
		$src = $prefix . '/' . $this->_path_ . '/' . $this->getFilename();
		return $src;
	}

	public function toHTML($prefix = '')
	{
		return HTMLTag::img($this->getThumbnail($prefix), [
//			'width' => 256,
			'height' => 256/2,
			'style' => [
				'max-height' => '128px',
			],
			'class' => 'meta',
			'data-id' => 'md5-' . md5($this->getFilename()),
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

	public function getOriginal($prefix = '')
	{
		$path = str_replace('__', ':/', $this->_path_);
		$path = trimExplode('_', $path);
		$path = implode('/', $path);
//		$path = new Path($path);
//		$path = $path->getURL();
		return $prefix . cap($path) . $this->FileName;
	}

	public function __debugInfo()
	{
		return $this->props;
	}

	protected function gps2Num($coordPart)
	{
		$parts = explode('/', $coordPart);
		if (count($parts) <= 0)
			return 0;
		if (count($parts) == 1)
			return $parts[0];
		return floatval($parts[0]) / floatval($parts[1]);
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

		$lat_direction = ($GPSLatitudeRef == 'W' or $GPSLatitudeRef == 'S') ? -1 : 1;
		$lon_direction = ($GPSLongitudeRef == 'W' or $GPSLongitudeRef == 'S') ? -1 : 1;

		$latitude = $lat_direction * ($lat_degrees + ($lat_minutes / 60) + ($lat_seconds / (60*60)));
		$longitude = $lon_direction * ($lon_degrees + ($lon_minutes / 60) + ($lon_seconds / (60*60)));

		return array($latitude, $longitude);
	}

	public function getFlickr()
	{
		$farm_id = $this->props['flickr_farm'];
		$server_id = $this->props['flickr_server'];
		$id = $this->props['filename'];
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

}
