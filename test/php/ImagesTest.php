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

	public function test_since()
	{
		$request = new Request();
		$request->set('since', '2020-05-10 22:28:05');
		$since = $request->getTimestampFromString('since');
		$images = new Images($this->db);
		$where = $images->getWhere($since);
//		debug($where);
		$this->assertCount(2, $where);
	}

	public function test_1()
	{
		$images = new Images($this->db);
		$result = $images->index();
		debug($result);
		$this->assertEquals('ok', $result->json->status);
	}

}
