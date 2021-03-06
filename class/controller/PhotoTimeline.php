<?php

use League\Flysystem\Filesystem;

/**
 * Class PhotoTimeline
 * @see https://www.soas.ac.uk/centenary/timeline/full97626.jpg
 * This is the original approach where metadata for all images in a folder
 * is stored in meta.json. Not working anymore, not sure if this needs to be fixed.
 * @deprecated
 */
class PhotoTimeline extends AppController
{

	protected $fileSystem;

	protected $prefix;

	protected $prefixURL;

	/**
	 * Inject("MetaSet4Thumbs")
	 * @var MetaSet
	 */
	protected $set;

	public function __construct(Filesystem $fileSystem, MetaSet $set)
	{
        parent::__construct();
        $this->fileSystem = $fileSystem;
		$this->prefix = $fileSystem->getAdapter()->getPathPrefix();
		$this->prefix = realpath($this->prefix);
		$this->prefixURL = substr(
			$this->prefix,
			strlen($_SERVER['DOCUMENT_ROOT']) + 1
		);
		$this->prefixURL = str_replace('\\', '/', $this->prefixURL);
//		debug($this->prefix, $_SERVER['DOCUMENT_ROOT'], $this->prefixURL);
		$this->set = $set;
	}

	public function __invoke()
	{
		$content = [];
//		debug($set->size());
//		$times = $set->groupBy('FileDateTime');


		$timelineService = new TimelineService($this->prefixURL);
		$imageFiles = $this->set->getLinear();
		$table = $timelineService->getTable($imageFiles);

		$content[] = new slTable($table, [
			'class' => 'table is-fullwidth'
		]);

		$content[] = '<hr>';

		$content[] = new HTMLTag('p', [], 'Total Files: ' . sizeof($imageFiles));
		$totalSize = array_reduce($imageFiles, static function ($acc, Meta $meta) {
			return $acc + $meta->getSize();
		}, 0);
		$bytes = new Bytes($totalSize);
		$content[] = new HTMLTag('p', [], 'Total Size: ' . $bytes->renderDynamic());

		return $this->template($content);
	}

}
