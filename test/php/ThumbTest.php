<?php


class ThumbTest extends MyTestCase
{

	public function test_mp4()
	{
		$meta = new MetaForSQL([
//			'path' => __DIR__ . '/Stefan/20150605_190818.mp4',
			'path' => __DIR__ . '/mp4/has-location.mp4',
		]);
		$meta->source = 1;
		$meta->sourceInstance = new Source(['path' => Request::isWindows() ? '' : '/']);
		$t = new VideoParser($meta->getFullPath());
		$json = $t->probe();
		debug($json);
		$this->assertEquals('+37.4034-122.1201/', $json->format->tags->location);
//		debug($t->log);
	}

}
