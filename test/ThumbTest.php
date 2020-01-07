<?php


class ThumbTest extends PHPUnit\Framework\TestCase
{

	public function test_mp4()
	{
		$meta = new MetaForSQL([
			'path' => __DIR__ . '/Stefan/20150605_190818.mp4',
		]);
		$meta->source = 1;
		$meta->sourceInstance = new Source(['path' => '/']);
		$t = new Thumb($meta);
		$json = $t->probe();
		debug($json);
		debug($t->log);
	}

}
