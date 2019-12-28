<?php

namespace App\CLI;

use App\Service\ScanDir;
use Bernard\Consumer;
use Bernard\Driver\Predis\Driver;
use Bernard\Message\PlainMessage;
use Bernard\Queue\RoundRobinQueue;
use Bernard\QueueFactory\PersistentFactory;
use Bernard\Router\ClassNameRouter;
use Bernard\Router\ReceiverMapRouter;
use Bernard\Serializer;
use Predis\Client;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Worker
{

    /**
     * @var Client
     */
    protected $predis;

    protected $container;

    public function __construct(Client $predis, ContainerInterface $container)
    {
        $this->predis = $predis;
        $this->container = $container;
    }

    public function process()
    {
        $driver = new Driver($this->predis);

        $factory = new PersistentFactory($driver, new Serializer());

        $eventDispatcher = new EventDispatcher();

//        $router = new ClassNameRouter([
//            ScanDir::class => $this->container->get(ScanDir::class),
//        ]);

        $router = new ReceiverMapRouter([
//            'send-newsletter' => static function ($p1, $p2) {
//                echo $p1, PHP_EOL;
//                echo $p2, PHP_EOL;
//            },
            'SendNewsletter' => $this,
            'ScanDir' => $this,
        ]);

        // Create a Consumer and start the loop.
        $consumer = new Consumer($router, $eventDispatcher);

        // The second argument is optional and is an array
        // of options. Currently only ``max-runtime`` is supported which specifies the max runtime
        // in seconds.
//        $queue = $factory->create('send-newsletter');
        $queue = new RoundRobinQueue([
            $factory->create('send-newsletter'),
            $factory->create('scan-dir'),
        ]);
        echo 'Listening...', PHP_EOL;
        $consumer->consume($queue, [
        ]);
    }

    public function sendNewsletter(PlainMessage $message)
    {
        echo __METHOD__, PHP_EOL;
        print_r($message->all());
    }

    public function scanDir(PlainMessage $message)
    {
        echo __METHOD__, PHP_EOL;
        print_r($message->all());
    }
}
