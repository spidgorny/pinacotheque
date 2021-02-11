<?php

//print_r($_SERVER);

require_once __DIR__.'/bootstrap.php';


function getController()
{
	if (PHP_SAPI === 'cli') {
		$c = $_SERVER['argv'][1];
		// php index.php --source 2 ScanMetaSetWidth
		if (str_startsWith($c, '--')) {
			$c = end($_SERVER['argv']);
		}
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
	return $c;
}

try {
	$c = getController();
	llog('Controller: ', $c);

	$container = getContainer();
	/** @var AppController $o */
	$o = $container->get($c);
	$o->setContainer($container);
	$content = $o();
	$content = MergedContent::mergeStringArrayRecursive($content);
	echo $content;
} catch (Exception $e) {
	http_response_code (500);
	llog(get_class($e), $e->getMessage(), $e->getFile(), $e->getLine());
	require __DIR__.'/template/404.php';
}
