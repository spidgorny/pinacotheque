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

}
