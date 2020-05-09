<?php

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ImagesTest extends TestCase
{

	protected $db;

	public function setUp()
	{
		/** @var ContainerInterface */
		$container = require(__DIR__ . '/../bootstrap.php');
		$this->db = $container->get(DBInterface::class);
	}

	public function test_1()
	{
		$images = new Images($this->db);
		$result = $images->index();
		debug($result);
		$this->assertTrue(true);
	}

}
