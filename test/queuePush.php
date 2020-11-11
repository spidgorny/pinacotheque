<?php

use Bernard\Message\PlainMessage;
use Bernard\Producer;

$container = require __DIR__.'/../bootstrap.php';

$producer = $container->get(Producer::class);

function sendJobFromCLI(Producer $producer)
{
	$JobType = ifsetor($_SERVER['argv'][1], 'SendNewsletter');
	$message = new PlainMessage($JobType, [
		'newsletterId' => 12,
	]);

	//$queue = 'send-newsletter';
	//$queue = $factory->create('send-newsletter');
	$producer->produce($message);
	//$producer->produce(new PlainMessage('asd'));
}

$message = new PlainMessage(ScanDir::class, ['dir' => '/tmp']);
$producer->produce($message);
echo 'produced', "\t", $message->getName(), PHP_EOL;
