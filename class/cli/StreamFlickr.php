<?php

class StreamFlickr extends AppController
{

	/**
	 * @var PhotoGPS
	 */
	protected $photo;

	public function __construct(PhotoGPS $photo)
	{
		$this->photo = $photo;
	}

	/**
	 * @throws Exception
	 */
	public function __invoke()
	{
		$per = new Percent(14122721);	// lines in that file
		$csv = new CsvIteratorWithHeader(__DIR__.'/../../data/photo_metadata.csv');
		$csv->escape = chr(0);	// has to be something
		foreach ($csv as $row) {
			$p = $this->photo->fetchByFilename($row['id']);
			if (!$p) {
				$this->photo->insertFlickr($row['id'], $row['latitude'], $row['longitude'], $row['date_taken'], $row['date_uploaded'], $row['flickr_secret'], $row['flickr_server'], $row['flickr_farm'], $row['title']);
			}

			try {
				$meta = new Meta($row);
				$url = $meta->getFlickr();
				echo $url, PHP_EOL;
				$fetch = new URLGet($url);
				$binary = $fetch->fetchAny();
//				echo strlen($binary), PHP_EOL;
				if ($binary) {
					file_put_contents(__DIR__ . '/../../data/flickr/' . $meta->getFilename(), $binary);
				}
			} catch (Exception $e) {}

			$per->inc();
			if ($per->changed()) {
				echo $per->get(), '%', PHP_EOL;
			}
		}
	}

}
