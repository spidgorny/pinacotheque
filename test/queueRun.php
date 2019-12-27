<?php

use Bernard\Consumer;
use Bernard\Message\PlainMessage;
use Bernard\Producer;
use Bernard\QueueFactory\PersistentFactory;
use Bernard\Router\ClassNameRouter;
use Bernard\Serializer;
use Bernard\Driver\Predis\Driver;
use Predis\Client;
use Symfony\Component\EventDispatcher\EventDispatcher;

require_once __DIR__.'/../bootstrap.php';

$predis = new Client('tcp://127.0.0.1', array(
    'prefix' => 'bernard:',
));

$driver = new Driver($predis);

$factory = new PersistentFactory($driver, new Serializer());

// $router = see bellow
$eventDispatcher = new EventDispatcher();

$router = new ClassNameRouter([
    SendNewsletter::class => new SendNewsletter(),
]);
// Create a Consumer and start the loop.
$consumer = new Consumer($router, $eventDispatcher);

// The second argument is optional and is an array
// of options. Currently only ``max-runtime`` is supported which specifies the max runtime
// in seconds.
$consumer->consume($factory->create('send-newsletter'), [
]);

class SendNewsletter
{

    public function __construct()
    {
        echo __METHOD__, PHP_EOL;
    }

}
