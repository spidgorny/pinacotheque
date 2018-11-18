<?php

require_once __DIR__.'/bootstrap.php';

if (php_sapi_name() == 'cli') {
	$c = $_SERVER['argv'][1];
} else {
	$c = ifsetor($_SERVER['PATH_INFO'], PhotoTimeline::class);
	$c = first(trimExplode('/', $c));
}
$o = $container->get($c);
$content = $o();
if (is_array($content)) {
	$content = implode(PHP_EOL, $content);
}
echo $content;
