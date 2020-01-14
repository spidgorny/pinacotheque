<?php

namespace App\Service;

use ImageParser;

class ImageParserTest extends \MyTestCase
{

	public function test_width_25k()
	{
		$ip = ImageParser::fromFile("/Volumes/mybook/marina/MarinaPhotoBackup/TempOlga2017/Slawa/IMG_20170711_195304.jpg");
		$meta = $ip->getMeta();
		debug($meta);
	}

}
