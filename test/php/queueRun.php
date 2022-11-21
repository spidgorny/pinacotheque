<?php

use Bernard\Consumer;
use Bernard\Message\PlainMessage;
use Bernard\QueueFactory\PersistentFactory;
use Bernard\Router\ClassNameRouter;
use Bernard\Router\ReceiverMapRouter;
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

//$router = new ClassNameRouter([
//    'send-newsletter' => new NewsletterProcessor(),
//    'SendNewsletter' => new NewsletterProcessor(),
//    NewsletterProcessor::class => new NewsletterProcessor(),
//]);

$router = new ReceiverMapRouter([
//    'send-newsletter' => new NewsletterProcessor(),
    'SendNewsletter' => new NewsletterProcessor(),
]);

// Create a Consumer and start the loop.
$consumer = new Consumer($router, $eventDispatcher);

// The second argument is optional and is an array
// of options. Currently only ``max-runtime`` is supported which specifies the max runtime
// in seconds.

//$queue = 'send-newsletter';
$queue = $factory->create('send-newsletter');
$consumer->consume($queue, [
]);

class NewsletterProcessor
{

    public function __construct()
    {
        echo __METHOD__, PHP_EOL;
    }

    public function sendNewsletter(PlainMessage $message)
    {
        echo __METHOD__, PHP_EOL;
    }

}
