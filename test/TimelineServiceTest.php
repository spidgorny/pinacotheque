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
		$this->assertGreaterThan(15, sizeof($table));
	}

	public function test_groupByDate()
	{
		$metaSet = new MetaSet($this->container->get('FlyThumbs'));
		$ts = new TimelineService('');
		$table = $ts->groupByYearMonth($metaSet->getLinear());

		$years = array_keys($table);
		$yearsKeys = [];
		foreach ($years as $y) {
			$monthCount = array_map(function ($monthData) {
				return sizeof($monthData);
			}, $table[$y]);
			$yearsKeys[$y] = $y.': '.implode(' ', $monthCount);
		}
		debug($yearsKeys);
		$from = first($years);
		echo 'From ', $from, PHP_EOL;
		$this->assertGreaterThan(2000, $from);
	}

}
