<?php

require_once __DIR__ . '/bootstrap.php';

$requestURI = $_SERVER['REQUEST_URI'];
$requestURI = urldecode($requestURI);	// %20 is not converted yet
if (file_exists(__DIR__ . '/' . $requestURI)) {
	return false; // serve the requested resource as-is.
}

//debug($requestURI, $_SERVER['PATH_INFO']);

$parts = trimExplode('/', $requestURI);

//$controller = $parts[0];
//echo $controller, PHP_EOL;

function debug_path()
{
	$pathParts = [__DIR__];
	foreach ($parts as $plus) {
		$pathParts[] = $plus;
		$path = implode('/', $pathParts);
		$isDir = is_dir($path);
		$isFile = is_file($path);
		echo $path, ': ', $isDir ? 'DIR' : ($isFile ? 'FILE' : ''), PHP_EOL;
		if (!$isDir && !$isFile) {
			break;
		}
	}
}

require __DIR__ . '/index.php';