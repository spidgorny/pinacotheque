<?php

class GeoQuery
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
		$leftBottom = [20, 50];
		$rightTop = [10, 60];

		$g = $this->photo->queryBoundingBox($leftBottom, $rightTop);
		debug(sizeof($g));
		foreach ($g as $m) {
			echo $m->getFlickr(), PHP_EOL;
		}
	}

}
