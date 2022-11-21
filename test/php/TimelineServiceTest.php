<?php

class TimelineServiceTest extends MyTestCase
{

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

	public function test_filterEmptyRows()
	{
		$ts = new TimelineService('');
		$table = [
			['a', 'b', 'c'],
			[],
			['d', 'e', 'f'],
		];
		$result = $ts->filterEmptyRows($table);
		$this->assertCount(2, $result);
	}

}
