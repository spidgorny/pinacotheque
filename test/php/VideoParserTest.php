<?php

namespace App\Service;

use VideoParser;

class VideoParserTest extends \MyTestCase
{

	public function test_metadata()
	{
		$vp = new VideoParser(__DIR__ . '/mp4/has-location.mp4');
		$meta = $vp->getMeta();
		debug($meta);
		$this->assertContains('DateTime', array_keys((array)$meta));
		$this->assertEquals('2015-07-22 15:30:16.000000Z', $meta->DateTime);
	}

}
