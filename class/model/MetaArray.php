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

	public function getSize()
	{
		return sizeof($this->data);
	}

	public function getFirst()
	{
		return first($this->data);
	}

	public function getAll()
	{
		return $this->data;
	}

	public function containsYearMonth($year, $month)
	{
		return array_reduce($this->data, function ($bool, Meta $meta) use ($year, $month) {
			return $bool ?: $meta->getYearMonth() == $year .'-'.$month;
		}, false);
	}

}
