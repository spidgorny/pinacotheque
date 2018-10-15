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

	public function queryBoundingBox($leftBottom, $rightTop)
	{
		$data = [];
		list($lat0, $lon0) = $leftBottom;
		list($lat1, $lon1) = $rightTop;
		$lat_min = min($lat0, $lat1);
		$lat_max = max($lat0, $lat1);
		$lon_min = min($lon0, $lon1);
		$lon_max = max($lon0, $lon1);
		$query = "SELECT * FROM photo 
		WHERE lat BETWEEN :lat0 AND :lat1
		AND lon BETWEEN :lon0 AND :lon1";
		$params = [
			':lat0' => $lat_min,
			':lat1' => $lat_max,
			':lon0' => $lon_min,
			':lon1' => $lon_max,
		];
//		debug($query, $params);
		$query = $this->db->query($query);
		$res = $query->execute($params);
		if ($res) {
			$data = $query->fetchAll(PDO::FETCH_ASSOC);
			foreach ($data as &$row) {
				$row = new Meta($row);
			}
		}
		return $data;
	}

}
