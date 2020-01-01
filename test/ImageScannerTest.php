<?php

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ImageScannerTest extends TestCase
{

	protected $db;

	public function setUp()
	{
		/** @var ContainerInterface */
		$container = require_once(__DIR__ . '/../bootstrap.php');
		$this->db = $container->get(DBLayerSQLite::class);
	}

	public function test_is()
	{
		$source = Source::findByID($this->db, 1);
		$source->path = __DIR__ . '/Stefan';
//		debug($source);

		$scanDir = new \App\Service\ScanDir($this->db, $source);
		$numFiles = $scanDir->numFiles();
		$this->assertEquals(369, $numFiles);

		$file = 'IMG_5473.JPG';	// relative to $source->path
		$dataStorage = getenv('DATA_STORAGE');
		$this->assertEquals('/Users/depidsvy/dev/pinacotheque/data', $dataStorage);
		$destinationRoot = path_plus($dataStorage, $source->thumbRoot);
		$this->assertEquals(realpath(__DIR__.'/../data/stefan'), $destinationRoot);

		$metaFile = new MetaFile($destinationRoot, $file);
		$is = new ImageScanner($source, $file, $destinationRoot, $metaFile, $this->db);
		$is(1);

		$this->assertFileExists($destinationRoot . '/meta.json');
		$json = json_decode(file_get_contents($destinationRoot . '/meta.json'));
		$this->assertTrue(isset($json->$file));
		$this->assertCount(82, (array)$json->$file);

		$this->assertFileExists($destinationRoot . '/' . $file);
		$size = getimagesize($destinationRoot . '/' . $file);
//		debug($size);
		$this->assertEquals(256, $size[0]);
		$this->assertEquals(171, $size[1]);
	}

}
