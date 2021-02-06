<?php

class RunVips extends AppController
{

	protected DBInterface $db;

	public function __construct(DBInterface $db)
	{
		parent::__construct();
		$this->db = $db;
	}

	public function test_old_generate()
	{
		$id = 1958580;
		$file = MetaForSQL::findByID($this->db, $id);
		$this->tryWith($file);
		unlink($file->getThumb()->getThumb());
		$this->tryWith($file);
	}

	public function tryWith(MetaForSQL $file)
	{
		llog($file->getPath(), $file->getFullPath());
		llog($file->getThumb()->getThumb());
		$tg = new ThumbGen($file);
		$tg->generate();
	}

	public function __invoke()
	{
		$source = Source::findByID($this->db, 7);
		if (!$source) {
			throw new Exception('No source');
		}
		$files = $source->getFiles(['type' => 'file']);

		foreach ($files as $i => $file) {
			if (!$file->isImage()) {
				continue;
			}
//			print_r($file);
//			llog($file->getDestination(), $file->getThumb()->exists());
			if ($file->getThumb()->exists()) {
				continue;
			}
			$timer = new Profiler();
			$cmd = ['vipsthumbnail', $file->getFullPath(), '--size', '256x', '-o', $file->getDestination()];
//			llog($cmd);
			$p = new Symfony\Component\Process\Process($cmd);
			$p->enableOutput();
			$p->start();
			$p->wait();
			if ($p->getErrorOutput()) {
				throw new Exception($p->getErrorOutput());
			}
			$output = $p->getOutput();
//			llog($output);
			if (!$file->getThumb()->exists()) {
				throw new Exception('vips failed to create ', $file->getDestination());
			}
			llog('+'.$timer->elapsed(),
//				$file->getPath(),
				$file->getDestination(),
				count($files) - $i,
			);
		}
	}

}
