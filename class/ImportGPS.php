<?php

class ImportGPS extends AppController
{

	function __invoke()
	{
		$path = 'C:\Users\depidsvy\web\slawa2018\pinacotheque\data\thumbs\C__Users_depidsvy_web_slawa2018_pinacotheque_data_flickr';
		$ms = new MetaSet(getFlySystem($path));
		$images = $ms->getLinear();
		foreach ($images as $filename => $meta) {
			if ($meta->GPSLatitude) {
				debug($meta->getLocation());
			}
		}
	}

}
