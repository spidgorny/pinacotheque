<?php

use PHPUnit\Framework\TestCase;

class MetaForSQLTest extends TestCase
{

	protected DBInterface $db;

	public function setUp()
	{
		static $container;
		if (!$container) {
			$container = require __DIR__ . '/../bootstrap.php';
		}
		$this->db = $container->get(DBInterface::class);
	}

	public function test_mtime_as_string_1()
	{
		$s = Source::findByID($this->db, 1);
//		print_r($s->toJson());
		$this->assertEquals(1, $s->id);
	}

	public function test_mtime_as_string_2()
	{
		$s = Source::findByID($this->db, 8);
		print_r($s->toJson());
	}

}
