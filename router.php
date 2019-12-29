<?php

use function GuzzleHttp\Psr7\parse_query;

require_once __DIR__ . '/bootstrap.php';

$requestURI = ifsetor($_REQUEST['uri'], $_SERVER['REQUEST_URI']);
$requestURI = urldecode($requestURI);	// %20 is not converted yet
$requestURI = trimExplode('?', $requestURI, 2);
$_REQUEST += parse_query($requestURI[1]);
$requestURI = first($requestURI);	// no params
$requestURI = trim($requestURI, '/');
error_log('ru: ' . $requestURI);
if (file_exists(__DIR__ . '/' . $requestURI)) {
//	debug($_SERVER);
	if (str_contains($_SERVER['SERVER_SOFTWARE'], 'Caddy')) {
//		$mime = mime_content_type($requestURI); // text/plain
//		$finfo = finfo_open(FILEINFO_MIME_TYPE);
//		$mime = finfo_file($finfo, $requestURI);
		$mime = new MIME();
		header('Content-Type: ' . $mime->mime_by_ext($requestURI));
		readfile($requestURI);
	}
	return false; // serve the requested resource as-is.
}

//debug($requestURI, $_SERVER['PATH_INFO']);

$parts = trimExplode('/', $requestURI);

//$controller = $parts[0];
//echo $controller, PHP_EOL;

function debug_path()
{
	$pathParts = [__DIR__];
	foreach ($pathParts as $plus) {
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

$_SERVER['REQUEST_URI'] = $requestURI;	// this is what makes it work
require __DIR__ . '/index.php';
