<?php

class ImportGPS extends AppController
{

	/**
	 * @var PhotoGPS
	 */
	protected $photo;

	public function __construct(PhotoGPS $photo)
	{
		$this->photo = $photo;
	}

	function __invoke()
	{
		$path = 'C:\Users\depidsvy\web\slawa2018\pinacotheque\data\thumbs\C__Users_depidsvy_web_slawa2018_pinacotheque_data_flickr';
		$ms = new MetaSet(getFlySystem($path));
		$images = $ms->getLinear();
		foreach ($images as $filename => $meta) {
			if ($meta->GPSLatitude) {
				$location = $meta->getLocation();
				debug($location);
				$row = $this->photo->fetchByFilename($filename);
				if (!$row) {
					$this->photo->insert($filename, $location[0], $location[1]);
				}
			}
		}
	}

}
