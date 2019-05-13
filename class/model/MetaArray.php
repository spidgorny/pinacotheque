<?php

class MetaArray
{

	/** @var Meta[] */
	protected $data;

	public function __construct(array $data)
	{
		$this->data = $data;
	}

	public function getGps()
	{
		$places = [];
		foreach ($this->data as $meta) {
			$loc = $meta->getLocation();
			if ($loc) {
				$places[] = $meta->getAll() + [
						'lat' => $loc[0],
						'lon' => $loc[1],
					];
			}
		}
		return $places;
	}

}
