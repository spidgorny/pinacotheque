<?php

require_once __DIR__.'/bootstrap.php';

if (php_sapi_name() == 'cli') {
	$c = $_SERVER['argv'][1];
} else {
	$pathInfo = $_SERVER['PATH_INFO'];
	$requestURI = $_SERVER['REQUEST_URI'];
	$c = ifsetor($pathInfo, ifsetor($requestURI, PhotoTimeline::class));
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
