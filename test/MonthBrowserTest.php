<?php


use DI\ContainerBuilder;

class MonthBrowserTest extends PHPUnit\Framework\TestCase
{

	/**
	 * @Inject
	 * @var MonthBrowser
	 */
	var $monthBrowser;

	public function setUp() {
		$builder = new ContainerBuilder();
		$builder->useAnnotations(true);
		$builder->addDefinitions(__DIR__.'/../definitions.php');
		$this->container = $builder->build();

		$this->container->injectOn($this);

		parent::setUp();
	}

	public function testGetFoldersInMetaset()
	{
		$html = $this->monthBrowser->__invoke();
	}
}
