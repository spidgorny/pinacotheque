<?php

class InitDB extends AppController
{

	function __invoke()
	{
		$db = new PDO('sqlite:'.__DIR__.'/../data/geodb.sqlite');
		$res = $db->query('SELECT * FROM photo');
		if (!$res) {
			throw new Exception($db->errorInfo()[2]);
		}
		foreach ($res as $row) {
			echo $row;
		}
	}

}
