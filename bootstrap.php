<?php

error_reporting(E_ALL);
ini_set('display_errors', true);

require_once __DIR__ . '/autoload.php';

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
	/** @noinspection ForgottenDebugOutputInspection */
	print_r($options);
	$context = stream_context_create($options);
	return $context;
}

function __($a)
{
	return $a;
}

$llogHistory = [];
function llog(...$msg)
{
	global $llogHistory;
	if (count($msg) === 1 && is_scalar($msg[0])) {
		/** @noinspection ForgottenDebugOutputInspection */
		error_log(implode(', ', $msg));
		$llogHistory[] = implode(', ', $msg);
	} else {
		$msg = count($msg) === 1 ? first($msg) : $msg;	// prevend extra [] around single var
		$jsonOptions = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS | JSON_THROW_ON_ERROR | JSON_UNESCAPED_LINE_TERMINATORS;
		$string = json_encode($msg, $jsonOptions);
		if (strlen($string) > 70) {
			/** @noinspection JsonEncodingApiUsageInspection */
			$string = json_encode($msg, JSON_PRETTY_PRINT | $jsonOptions);
		}
		/** @noinspection ForgottenDebugOutputInspection */
		error_log($string);
		$llogHistory[] = $string;
	}
}

function getPathToThumbsFrom($index)
{
	$storageFolder = ifsetor($_SERVER['argv'][$index]);
	if (!$storageFolder) {
		throw new RuntimeException('php ScanOneFile /path/to/source <project name>');
	}
	if ($storageFolder[0] !== '/') {
		$DATA_STORAGE = getenv('DATA_STORAGE');
		if (!$DATA_STORAGE) {
			throw new RuntimeException('Make .env with DATA_STORAGE=' . getcwd());
		}
		$thumbsPath = cap($DATA_STORAGE) . $storageFolder;
	} else {
		$thumbsPath = $storageFolder;    // absolute path provided
	}
	if (!is_dir($thumbsPath)) {
		throw new RuntimeException($thumbsPath . ' is not a folder');
	}
//	debug($storageFolder, $thumbsPath);
	return $thumbsPath;
}

if (PHP_SAPI !== 'cli') {
	$headers = apache_request_headers();
//	llog($headers);
	$userAgent = $headers['User-Agent'];
	if (!str_startsWith($userAgent, 'Apache-HttpClient')) {    // PHPStorm
		$whoops = new \Whoops\Run;
		$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
		$whoops->register();
	}
}

function getContainer()
{
	static $container;
	if (!$container) {
		$builder = new DI\ContainerBuilder();
		$builder->addDefinitions(__DIR__ . '/definitions.php');
		$builder->useAnnotations(true);
		$container = $builder->build();

		//$db = $container->get(DBInterface::class);
		//$db->logToLog = true;
	}
	return $container;
}
