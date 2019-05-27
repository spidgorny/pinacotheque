<?php

class MetaTest extends PHPUnit\Framework\TestCase
{

//	protected $prefix = 'C:\Users\depidsvy\web\slawa2018\pinacotheque\data\thumbs';

	function test_getOriginal()
	{
		$meta = new Meta([
			'FileName' => "04fXWgj.jpg",
			'FileDateTime' => 1437887184,
			'FileSize' => 373251,
			'FileType' => 2,
			'MimeType' => "image\/jpeg",
			'SectionsFound' => "",
			'COMPUTED' => [
				'html' => "width=\"2560\" height=\"1600\"",
				'Height' => 1600,
				'Width' => 2560,
				'IsColor' => 1,
			],
			'_path_' => 'C__Users_depidsvy_web_slawa2018_pinacotheque_data_ThomasGasson',
		]);
		$original = $meta->getOriginal();
		//debug($meta->_path_, $original);
		$this->assertEquals('ImgProxy?path=C__Users_depidsvy_web_slawa2018_pinacotheque_data_ThomasGasson&file=04fXWgj.jpg',
			$original);
	}

	public function test_yearMonth()
	{
		$meta = new Meta([
			'FileDateTime' => 1437887184,
		]);
		$yearMonth = $meta->yearMonth();
		//debug($yearMonth);
		$this->assertEquals('2015-07', $yearMonth);
	}

	public function test_yearMonth2()
	{
		$meta = new Meta([
			'FileDateTime' => -1,
			'DateTimeOriginal' => '2008:05:11 08:30:55',
		]);
		$yearMonth = $meta->yearMonth();
		debug($yearMonth);
		$this->assertEquals('2008-05', $yearMonth);
	}
}
