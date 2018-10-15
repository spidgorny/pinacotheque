<?php

class StreamFlickr extends AppController
{

	/**
	 * @var PDO
	 */
	protected $db;

	/**
	 * @var PhotoGPS
	 */
	protected $photo;

	public function __construct(PDO $db, PhotoGPS $photo)
	{
		$this->db = $db;
		$this->photo = $photo;
	}

	/**
	 * @throws Exception
	 */
	public function __invoke()
	{
		$per = new Percent(14122721);	// lines in that file
		$csv = new CsvIteratorWithHeader(__DIR__.'/../data/photo_metadata.csv');
		foreach ($csv as $row) {
			$p = $this->photo->fetchByFilename($row['id']);
			if (!$p) {
				$this->photo->insertFlickr($row['id'], $row['latitude'], $row['longitude'], $row['date_taken'], $row['date_uploaded'], $row['flickr_secret'], $row['flickr_server'], $row['flickr_farm'], $row['title']);
			}
			$per->inc();
			if ($per->changed()) {
				echo $per->get(), '%', PHP_EOL;
			}
		}
	}

}
