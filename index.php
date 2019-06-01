<?php

require_once __DIR__.'/bootstrap.php';

if (php_sapi_name() == 'cli') {
	$c = $_SERVER['argv'][1];
	if (!$c) {
		throw new RuntimeException('Usage: php index.php <Controller>');
	}
} else {
	session_start();
	$pathInfo = ifsetor($_SERVER['PATH_INFO']);
	$requestURI = ifsetor($_SERVER['REQUEST_URI']);
	error_log($requestURI);
	$c = $pathInfo ?: ($requestURI ?: PhotoTimeline::class);
	//debug($pathInfo, $requestURI, $c);
	$c = first(trimExplode('/', $c));
	$c = $c ?: PhotoTimeline::class;
}
$o = $container->get($c);
$content = $o();
if (is_array($content)) {
	$content = implode(PHP_EOL, $content);
}
echo $content;
