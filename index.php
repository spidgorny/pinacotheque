<?php

require_once __DIR__.'/bootstrap.php';

if (php_sapi_name() == 'cli') {
	$c = $_SERVER['argv'][1];
	if (!$c) {
		throw new RuntimeException('Usage: php index.php <Controller>');
	}
} else {
	$start = microtime(true);
	session_start();
	error_log('Session loading: '.(microtime(true) - $start));
	$pathInfo = ifsetor($_SERVER['PATH_INFO']);
	$requestURI = ifsetor($_SERVER['REQUEST_URI']);
//	error_log($pathInfo);
	error_log($requestURI);
	$c = $pathInfo ?: ($requestURI ?: PhotoTimeline::class);
	//debug($pathInfo, $requestURI, $c);
	$c = first(trimExplode('/', $c));
	$c = $c ?: PhotoTimeline::class;
}

/** @var AppController $o */
$o = $container->get($c);
$o->setContainer($container);
$content = $o();
if (is_array($content)) {
	$content = implode(PHP_EOL, $content);
}
echo $content;
