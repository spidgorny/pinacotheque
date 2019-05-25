<?php


use DI\ContainerBuilder;

class MetaSetTest extends PHPUnit\Framework\TestCase
{

	private $container;

	/**
	 * @Inject("FlyThumbs")
	 * @var \League\Flysystem\Filesystem
	 */
	public $fly;

	public function setUp() {
		$builder = new ContainerBuilder();
		$builder->useAnnotations(true);
		$builder->addDefinitions(__DIR__.'/../definitions.php');
		$this->container = $builder->build();

		$this->container->injectOn($this);

		parent::setUp();
	}

	public function testReadMeta()
	{
		$set = new MetaSet($this->fly);
		foreach ($set->get() as $path => $info) {
			$info1 = first($info);
			$this->assertNotFalse($info1);
		}
	}
}
