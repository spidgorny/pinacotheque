<?php

use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

require_once __DIR__.'/../bootstrap.php';

$phpBinaryFinder = new PhpExecutableFinder();
$php = $phpBinaryFinder->find();

$pathFile = 'D:\Pictures\OnePlus3T/2018-10/IMG_20181006_155600.jpg';
$short = 'OnePlus3T/2018-10/IMG_20181006_155600.jpg';
$p = new Process([$php, '../index.php', ScanOneFile::class, "d:\pictures", $pathFile, $short]);
echo $p->getCommandLine(), PHP_EOL;
$p->run(function ($type, $content) {
	echo $content;
});
