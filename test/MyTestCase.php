<?php

use DI\ContainerBuilder;

class MyTestCase extends \PHPUnit\Framework\TestCase
{

	/**
	 * @var \Psr\Container\ContainerInterface
	 */
	var $container;

	public function setUp() {
		$builder = new ContainerBuilder();
		$builder->useAnnotations(true);
		$builder->addDefinitions(__DIR__.'/../definitions.php');
		$this->container = $builder->build();

		$this->container->injectOn($this);

		parent::setUp();
	}

}
