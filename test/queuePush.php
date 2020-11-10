<?php

use Bernard\Message\PlainMessage;
use Bernard\Producer;
use Bernard\QueueFactory\PersistentFactory;
use Bernard\Serializer;
use Bernard\Driver\Predis\Driver;
use Predis\Client;
use Symfony\Component\EventDispatcher\EventDispatcher;

require_once __DIR__.'/../bootstrap.php';

$predis = new Client('tcp://' . getenv('REDIS_HOST'), [
    'prefix' => 'bernard:',
]);

$driver = new Driver($predis);

$factory = new PersistentFactory($driver, new Serializer());
$producer = new Producer($factory, new EventDispatcher());

$JobType = ifsetor($_SERVER['argv'][1], 'SendNewsletter');
$message = new PlainMessage($JobType, [
    'newsletterId' => 12,
]);

//$queue = 'send-newsletter';
//$queue = $factory->create('send-newsletter');
$producer->produce($message);
$producer->produce(new PlainMessage('asd'));
$producer->produce(new PlainMessage(ScanDir::class));
echo 'produced', "\t", $JobType, PHP_EOL;
