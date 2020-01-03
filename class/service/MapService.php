<?php

class MapService
{

	public function __construct()
	{
	}

	public function __invoke(array $set)
	{
		$content = [];
		$ma = new MetaArray($set);
		if ($ma->getGps()) {
			$content[] = '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.5.1/dist/leaflet.css"
	   integrity="sha512-xwE/Az9zrjBIphAcBb3F6JVqxf46+CDLwfLMHloNu6KEQCAWi6HcDUbeOfBIptF7tcCzusKFjFw2yuvEpDL9wQ=="
	   crossorigin=""/>';
			$content[] = '<script src="https://unpkg.com/leaflet@1.5.1/dist/leaflet.js"
	   integrity="sha512-GffPMF3RvMeYyc1LWMHtK8EbPv0iNZ8/oTtHPx9/cc2ILxQ+u905qIwdpULaqDkyBKgOaB57QTMg7ztg8Jm2Og=="
	   crossorigin=""></script>';
			$content[] = '<div id="mapid"></div>';
			$content[] = '<style>#mapid { height: 748px}</style>';
			$content[] = '<script src="www/js/mapForMonth.js"></script>';
		} else {
			$content[] = 'No GPS info in images';
		}
		return $content;
	}

}
