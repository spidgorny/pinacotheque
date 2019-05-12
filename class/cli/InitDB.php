<?php

class InitDB extends AppController
{

	/**
	 * @var PDO
	 */
	protected $db;

	public function __construct(PDO $pdo)
	{
		$this->db = $pdo;
	}

	function __invoke()
	{
		$res = $this->db->query('SELECT * FROM photo');
		if (!$res) {
			throw new Exception($this->db->errorInfo()[2]);
		}
		foreach ($res as $row) {
			echo $row;
		}
	}

}
