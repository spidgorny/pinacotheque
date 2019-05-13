<?php

use League\Flysystem\Filesystem;

/**
 * Class PhotoTimeline
 * @see https://www.soas.ac.uk/centenary/timeline/full97626.jpg
 */
class PhotoTimeline extends AppController
{

	protected $fileSystem;

	protected $prefix;

	protected $prefixURL;

	public function __construct(Filesystem $fileSystem)
	{
		$this->fileSystem = $fileSystem;
		$this->prefix = $fileSystem->getAdapter()->getPathPrefix();
		$this->prefix = realpath($this->prefix);
		$this->prefixURL = substr(
			$this->prefix,
			strlen($_SERVER['DOCUMENT_ROOT'])+1
		);
		$this->prefixURL = str_replace('\\', '/', $this->prefixURL);
//		debug($this->prefix, $_SERVER['DOCUMENT_ROOT'], $this->prefixURL);
	}

	public function __invoke()
	{
		$content = [];
		$set = new MetaSet(getFlySystem($this->prefix));
//		debug($set->size());
//		$times = $set->groupBy('FileDateTime');


		$timelineService = new TimelineService($this->prefixURL);
		$table = $timelineService->getTable($set->getLinear());

		$content[] = new slTable($table, [
			'class' => 'table is-fullwidth'
		]);
		return $this->template($content);
	}

}
