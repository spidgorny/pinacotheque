<?php

namespace Test\ScanDir;

use PHPUnit\Framework\TestCase;
use ScanDir\FlySystem;
use ScanDir\ScanDirRecursive;

class FlySystemTest extends TestCase
{

	public function testScandirRaw()
	{
		$fly = new FlySystem(__DIR__);
		$files = $fly->scandirRaw();
//		print_r($files);
		$this->assertGreaterThan(10, $files);
		foreach ($files as $file) {
//			echo '*', "\t", $file['path'], PHP_EOL;
			$this->assertArrayHasKey('type', $file);
			$this->assertArrayHasKey('path', $file);
			$this->assertArrayHasKey('timestamp', $file);
			$this->assertNotContains(__DIR__, $file['path']);
		}
	}

	public function testScandirFile()
	{
		$fly = new FlySystem(__DIR__);
		$files = $fly->scandir();
//		print_r($files);
		$this->assertGreaterThan(10, $files);
		foreach ($files as $file) {
//			echo '*', "\t", $file.'', PHP_EOL;
			$this->assertInstanceOf(\File::class, $file);
		}
	}

	public function testScandirCompare()
	{
		$fly = new FlySystem(__DIR__);
		$files1 = $fly->scandirRaw();
		$files2 = $fly->scandir();
		foreach ($files1 as $i => $file1) {
			$file2 = $files2[$i];
//			echo '*', "\t", $file1['path'], "\t", $file2.'', PHP_EOL;
			$this->assertEquals($file1['path'], $file2.'');
		}
	}

	public function xtestScandirListFilesIn()
	{
		$fly = new \ListFilesIn(__DIR__);
		$files = $fly->getArrayCopy();
//		print_r($files);
		$this->assertGreaterThan(10, $files);
		foreach ($files as $file) {
//			echo '*', "\t", $file.'', PHP_EOL;
			$this->assertInstanceOf(\File::class, $file);
		}
	}

	public function testScandirRecursive()
	{
		$scanner = new ScanDirRecursive(__DIR__);
		$files = $scanner->scandir();
		foreach ($files as $file) {
//			echo '*', "\t", $file.'', PHP_EOL;
			$this->assertInstanceOf(\File::class, $file);
		}
	}

	public function testScandirRecursiveCompare()
	{
		$fly = new FlySystem(__DIR__);
		$files = $fly->scandir();

		$scanner = new ScanDirRecursive(__DIR__);
		$files = $scanner->scandir();
		foreach ($files as $i => $file) {
			$file2 = $files[$i];
//			echo '*', "\t", $file.'', "\t", $file2, PHP_EOL;
			$this->assertEquals($file->getPathname(), $file2->getPathname());
		}
	}

}
