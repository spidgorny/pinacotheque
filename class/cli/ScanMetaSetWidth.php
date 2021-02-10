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
		llog('argv', $_SERVER['argv']);
		$opt = getopt('', ['skip:', 'source:']);
		llog('opt', $opt);

		$skip = (int)ifsetor($opt['skip'], 0);
		llog('skip', $skip);

		$where = [];
		if (isset($opt['source'])) {
			$where['source'] = $opt['source'];
		}

		$provider = new FileProvider($this->db);
		llog('querying...');
		$iterator = $provider->getAllFiles($where);
		llog('skipping...');
		$iterator->skip($skip);
		llog('processing...');

		$timer = new Profiler();

		$i = $skip;
		/** @var MetaForSQL $meta */
		foreach ($iterator as $meta) {
			$timer->restart();
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
//				llog($meta->ensureMeta());

				// video may not have any meta data
				if ($meta->isImage()) {
					break;
				}
			}

			echo $i, TAB, basename($meta->getPath()), '['.count($meta->getMeta()).']', TAB, $width . 'x' . $height, TAB, $timer->elapsed().' s', PHP_EOL;
//			if (!($i % 60)) {
//				echo TAB, $timer->elapsed(), PHP_EOL, $i, TAB;
//				$timer->restart();
//			}
			$i++;
		}
	}

}
