<?php

use Intervention\Image\Exception\NotReadableException;

class ShowThumb extends AppController
{

	protected $transparent1px = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

	protected $db;

	public function __construct(DBLayerSQLite $db)
	{
		parent::__construct();
		$this->db = $db;
	}

	public function __invoke()
	{
		session_write_close();
		try {
			return $this->index();
		} catch (Exception $e) {
			return $this->template([
				HTMLTag::div($e, ['class' => 'is-danger']),
			]);
		}
	}

	public function index()
	{
//		debug($_REQUEST);
//		exit;
		$file = $this->request->getTrim('file');
		if (!$file) {
			header('Content-Type: image/png');
			return base64_decode($this->transparent1px);
		}

		$meta = MetaForSQL::findByID($this->db, $file);
		$content[] = getDebug($meta);

		if (!$meta->id) {
			debug($this->db->getLastQuery());
			throw new Exception404('File with id=' . $file . ' not found');
		}

		$filePath = $meta->getFullPath();
		$content[] = 'File: ' . $filePath . BR;
		$content[] = 'Exists: ' . filesize($filePath) . BR;

		$thumb = new Thumb($meta);
		$content[] = getDebug($thumb);

		try {
			$thumb->getThumb();    // make it if doesn't exist
		} catch (NotReadableException $e) {
			$content[] = $e;
		}

		$content[] = HTMLTag::img(ShowThumb::href(['file' => $file]), [
			'border' => 1,
		]);

		$thumbPath = $thumb->getThumbPath();
		if ($this->request->getBool('d') || !$thumb->exists()) {
			return $content;
		}

		header('Content-Type: ' . mime_content_type($thumbPath));
		$this->request->setCacheable();
		readfile($thumbPath);
	}

}
