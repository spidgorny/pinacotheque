<?php

require_once __DIR__.'/bootstrap.php';

//debug($_SERVER);

if (php_sapi_name() === 'cli') {
	$c = $_SERVER['argv'][1];
	if (!$c) {
		throw new RuntimeException('Usage: php index.php <Controller>');
	}
} else {
	$start = microtime(true);
	session_start();
//	error_log('Session loading: '.(microtime(true) - $start));
	$pathInfo = ifsetor($_SERVER['PATH_INFO']);
	$requestURI = ifsetor($_SERVER['REQUEST_URI']);
//	error_log('pathInfo: ' . $pathInfo);
//	error_log('requestURI: ' . $requestURI);
	$c = $pathInfo ?: $requestURI;
	//debug($pathInfo, $requestURI, $c);
	$parts = parse_url($c);
//	debug($parts);
	$c = $parts['path'];
	$c = first(trimExplode('/', $c));
	$c = $c ?: Sources::class;
}

/** @var AppController $o */
$o = $container->get($c);
$o->setContainer($container);
$content = $o();
$content = MergedContent::mergeStringArrayRecursive($content);
echo $content;
