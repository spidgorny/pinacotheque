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
		$iterator = $provider->getAllFiles();
		$iterator->skip($skip);

		$timer = new Profiler();

		$i = $skip;
		/** @var MetaForSQL $meta */
		foreach ($iterator as $meta) {
			if ($meta->width && $meta->height) {
				continue;
			}
			$meta->injectDB($this->db);
			if (!$meta->hasMeta()) {
				$processor = ParserFactory::getInstance($meta);
				$parser = $processor->getParser();
				$metaData = $parser->getMeta();
				$is = new ImageScanner($meta, $this->db);
				$is->saveMetaToDB($metaData);
			}
			$meta->loadMeta();
			if ($meta->meta_error) {
				continue;
			}
			$width = $meta->getWidth();
			$height = $meta->getHeight();
//			echo str_pad($meta->id, 10), TAB, $meta->getExt(), TAB, $width, 'x', $height;
			if (!$width || !$height) {
				echo PHP_EOL;
				llog($meta->id, $meta->getFullPath());
				llog($meta->streams ?? null);
				llog($meta->streams[0] ?? null);
				llog($meta->streams[0]['width'] ?? null);
				llog($meta->streams[0]->width ?? null);
				llog($meta->streams[0][0]->width ?? null);
				llog($meta->props);
				break;
			}
			$meta->update([
				'width' => $width,
				'height' => $height,
			]);
			echo '.';
			if (!($i % 60)) {
				echo $timer->elapsed(), PHP_EOL, $i, TAB;
				$timer->restart();
			}
			$i++;
		}
	}

}
