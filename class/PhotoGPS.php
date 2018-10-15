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
		$res = $query->execute($filename);
		if ($res) {
			$data = $query->fetch(PDO::FETCH_ASSOC);
		}
		return $data;
	}

}
