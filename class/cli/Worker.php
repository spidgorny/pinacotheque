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
		$consumer->consume($queue, []);
	}

	public function sendNewsletter(PlainMessage $message)
	{
		echo __METHOD__, PHP_EOL;
		/** @noinspection ForgottenDebugOutputInspection */
		print_r($message->all());
	}

	public function scanDir(PlainMessage $message)
	{
		$startTime = microtime(true);
		try {
			echo __METHOD__, PHP_EOL;
			/** @noinspection ForgottenDebugOutputInspection */
			print_r($message->all());
			$dir = $message->get('dir');
			if (!$dir) {
				throw new \InvalidArgumentException('ScanDir worker needs $dir');
			}
			echo 'ScanDir [' . $dir . ']', PHP_EOL;
			if (!is_readable($dir)) {
				throw new \InvalidArgumentException('This dir is not readable');
			}
			echo 'readable', PHP_EOL;
			$db = $this->container->get(\DBInterface::class);
			echo $db, PHP_EOL;
			$source = \Source::findByPath($db, $dir);
			if (!$source) {
				$source = \Source::insert($db, $dir);
			}
			echo $source, PHP_EOL;
			$scanner = new ScanDir($db, $source);
			$scanner();
		} catch (\Exception $e) {
			echo '***', PHP_EOL;
			echo get_class($e), PHP_EOL;
			echo $e->getMessage(), PHP_EOL;
			echo $e->getFile(), ':', $e->getLine(), PHP_EOL;
			echo $e->getTraceAsString(), PHP_EOL;
			echo '***', PHP_EOL;
		}
		echo 'Done in ', microtime(true) - $startTime, PHP_EOL;
	}
}
