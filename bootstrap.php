<?php

require_once __DIR__.'/autoload.php';

function getContext()
{
	$options = [
		'http' => [
			'proxy' => getenv('https_proxy'),
			'request_fulluri' => true,
		],
		'https' => [
			'proxy' => getenv('https_proxy'),
			'request_fulluri' => true,
		],
	];
	print_r($options);
	$context = stream_context_create($options);
	return $context;
}

function __($a)
{
	return $a;
}

function llog($msg)
{
	error_log($msg);
}

function getPathToThumbsFrom($index)
{
	$storageFolder = ifsetor($_SERVER['argv'][$index]);
	if (!$storageFolder) {
		throw new RuntimeException('php ScanOneFile /path/to/source <project name>');
	}
	if ($storageFolder[0] != '/') {
		$DATA_STORAGE = getenv('DATA_STORAGE');
		if (!$DATA_STORAGE) {
			throw new RuntimeException('Make .env with DATA_STORAGE=' . getcwd());
		}
		$thumbsPath = cap($DATA_STORAGE) . $storageFolder;
	} else {
		$thumbsPath = $storageFolder;	// absolute path provided
	}
	if (!is_dir($thumbsPath)) {
		throw new RuntimeException($thumbsPath.' is not a folder');
	}
//	debug($storageFolder, $thumbsPath);
	return $thumbsPath;
}

$builder = new DI\ContainerBuilder();
$builder->addDefinitions(__DIR__.'/definitions.php');
$builder->useAnnotations(true);
$container = $builder->build();

return $container;
