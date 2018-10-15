<?php

class PhotoGPS
{

	/**
	 * @var PDO
	 */
	protected $db;

	public function __construct($db)
	{
		$this->db = $db;
	}

	public function fetchByFilename($filename)
	{
		$data = [];
		$query = $this->db->query("SELECT * FROM photo WHERE filename=:filename");
		$res = $query->execute([
			':filename' => $filename
		]);
		if ($res) {
			$data = $query->fetch(PDO::FETCH_ASSOC);
		}
		return $data;
	}

	/**
	 * @param string $filename
	 * @param float $lat
	 * @param float $lon
	 * @return bool
	 * @throws Exception
	 */
	public function insert($filename, $lat, $lon)
	{
		if (!$filename) {
			throw new InvalidArgumentException('No filename');
		}
		$query = $this->db->query("INSERT INTO photo (filename, lat, lon) VALUES (:filename, :lat, :lon)");
		if (!$query) {
			throw new Exception($this->db->errorInfo()[2]);
		}
		$res = $query->execute([
			':filename' => $filename,
			':lat' => $lat,
			':lon' => $lon
		]);
		return $res;
	}

	/**
	 * @param $filename
	 * @param $lat
	 * @param $lon
	 * @param $date_taken
	 * @param $date_uploaded
	 * @param $flickr_secret
	 * @param $flickr_server
	 * @param $flickr_farm
	 * @param $title
	 * @return bool
	 * @throws Exception
	 */
	public function insertFlickr($filename, $lat, $lon, $date_taken, $date_uploaded, $flickr_secret, $flickr_server, $flickr_farm, $title)
	{
		if (!$filename) {
			throw new InvalidArgumentException('No filename');
		}
		$query = $this->db->prepare("INSERT INTO photo 
		(filename, lat, lon, date_taken, date_uploaded, 
		flickr_secret, flickr_server, flickr_farm, title) VALUES 
		(:filename, :lat, :lon, :date_taken, :date_uploaded,
		:flickr_secret, :flickr_server, :flickr_farm, :title)");
		if (!$query) {
			throw new Exception($this->db->errorInfo()[2]);
		}
		$res = $query->execute([
			':filename' => $filename,
			':lat' => $lat,
			':lon' => $lon,
			':date_taken' => $date_taken,
			':date_uploaded' => $date_uploaded,
			':flickr_secret' => $flickr_secret,
			':flickr_server' => $flickr_server,
			':flickr_farm' => $flickr_farm,
			':title' => $title,
		]);
		return $res;
	}

}
