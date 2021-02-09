<?php

class ScanMetaSetWidth extends AppController
{

	protected DBInterface $db;

	public function __construct(DBInterface $db)
	{
		parent::__construct();
		$this->db = $db;
	}

	public function index()
	{
		$skip = (int)ifsetor($_SERVER['argv'][2], 0);
		llog('skip', $skip);

		$provider = new FileProvider($this->db);
		llog('querying...');
		$iterator = $provider->getAllFiles();
		llog('skipping...');
		$iterator->skip($skip);
		llog('processing...');

		$timer = new Profiler();

		$i = $skip;
		/** @var MetaForSQL $meta */
		foreach ($iterator as $meta) {
			if ($meta->width && $meta->height) {
				continue;
			}
			if ($meta->meta_error) {
				continue;
			}
			$meta->injectDB($this->db);
//			if (!$meta->hasMeta()) {
//				return $meta->ensureMeta();
//			}
			[$width, $height] = $meta->insertWidthHeight();

			if (!$width || !$height) {
				echo PHP_EOL;
				llog($meta->id, $meta->getFullPath());
				llog('exists', $meta->isFile());
//				llog($meta->props);
				llog($meta->ensureMeta());

				// video may not have any meta data
				if ($meta->isImage()) {
					break;
				}
			}

			echo '.';
			if (!($i % 60)) {
				echo TAB, $timer->elapsed(), PHP_EOL, $i, TAB;
				$timer->restart();
			}
			$i++;
		}
	}

}
