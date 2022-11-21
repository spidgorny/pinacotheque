<?php

class SetCorrectDateTimeNotTestTest extends \PHPUnit\Framework\TestCase
{

	protected DBInterface $db;
	protected SetCorrectDateTimeNotTest $sut;

	public function setUp(): void
	{
		parent::setUp();
		$this->db = getContainer()->get(DBInterface::class);
		$this->sut = new SetCorrectDateTimeNotTest($this->db);
	}

	public function test_timestamp()
	{
		$val = $this->sut->getDateTime('IMG_123.jpg', [
		], strtotime('2020-02-02 11:19:33'));
		llog($val);
		$this->assertEquals('2020-02-02 11:19:33', $val);
	}

	public function test_DateTime()
	{
		$val = $this->sut->getDateTime('IMG_123.jpg', [
			'DateTime' => '2020-01-01 13:18:22',
		], strtotime('2020-02-02 11:19:33'));
		llog($val);
		$this->assertEquals('2020-01-01 13:18:22', $val);
	}

	public function test_DateTimeOriginal()
	{
		$val = $this->sut->getDateTime('IMG_123.jpg', [
			'DateTimeOriginal' => '2020-01-01 13:18:22',
		], strtotime('2020-02-02 11:19:33'));
		llog($val);
		$this->assertEquals('2020-01-01 13:18:22', $val);
	}

	public function test_DateTimeDigitized()
	{
		$val = $this->sut->getDateTime('IMG_123.jpg', [
			'DateTimeDigitized' => '2020-01-01 13:18:22',
		], strtotime('2020-02-02 11:19:33'));
		llog($val);
		$this->assertEquals('2020-01-01 13:18:22', $val);
	}

	public function test_FileDateTime()
	{
		$val = $this->sut->getDateTime('IMG_123.jpg', [
			'FileDateTime' => '2020-01-01 13:18:22',
		], strtotime('2020-02-02 11:19:33'));
		llog($val);
		$this->assertEquals('2020-01-01 13:18:22', $val);
	}

	public function test_creation_time()
	{
		$val = $this->sut->getDateTime('IMG_123.jpg', [
			'format' => (object)[
				'tags' => (object)[
					'creation_time' => '2020-01-01 13:18:22',
				]
			]
		], strtotime('2020-02-02 11:19:33'));
		llog($val);
		$this->assertEquals('2020-01-01 13:18:22', $val);
	}

	public function test_TelegramVideo()
	{
		$val = $this->sut->getDateTime('dcim-samsung/Telegram Video/2_121388501773066338.mp4', [], strtotime('2020-02-02 11:19:33'));
		llog($val);
		$this->assertEquals('2020-02-02 10:19:33', $val);
	}

	public function test_FileWithDate()
	{
		$val = $this->sut->getDateTime('20200101_131811.mp4', [], strtotime('2020-02-02 11:19:33'));
		llog($val);
		$this->assertEquals('2020-01-01 13:18:11', $val);
	}

	public function test_width_COMPUTED()
	{
		$meta = MetaForSQL::findByID($this->db, 3);
		$meta->loadMeta();
//		llog($meta);
		$meta->props['COMPUTED'] = (object)[
			"Width" => 4128,
			"Height" => 2322,
		];
		$width = $meta->getWidth();
		$height = $meta->getHeight();
//		llog($width, $height);
		$this->assertEquals(4128, $width);
		$this->assertEquals(2322, $height);
	}

}
