<?php


use DI\ContainerBuilder;

class TimelineServiceTest extends PHPUnit\Framework\TestCase
{

	var $container;

	public function setUp() {
		$builder = new ContainerBuilder();
		$builder->useAnnotations(true);
		$builder->addDefinitions(__DIR__.'/../definitions.php');
		$this->container = $builder->build();

		$this->container->injectOn($this);

		parent::setUp();
	}

	public function test_TimelineService()
	{
		$metaSet = new MetaSet($this->container->get('FlyThumbs'));
		$ts = new TimelineService('');
		$table = $ts->getTable($metaSet->getLinear());
		$this->assertGreaterThan(50, sizeof($table));
	}

}