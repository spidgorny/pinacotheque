<?php

use Intervention\Image\Exception\NotReadableException;
use Intervention\Image\Exception\NotWritableException;

class ShowThumb extends AppController
{

	protected $transparent1px = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

	/**
	 * @var DBInterface
	 */
	protected $db;

	public function __construct(DBInterface $db)
	{
		parent::__construct();
		$this->db = $db;
	}

	public function __invoke()
	{
		session_write_close();
		try {
			return parent::__invoke();
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
		$content[] = getDebug([
			'isImage' => $meta->isImage(),
			'isVideo' => $meta->isVideo(),
		]);

		if (!$meta->id) {
			debug($this->db->getLastQuery());
			throw new Exception404('File with id=' . $file . ' not found');
		}

		$filePath = $meta->getFullPath();
		$content[] = getDebug([
			'File' => $filePath,
			'Exists' => filesize($filePath),
			'ffmpeg' => getenv('ffmpeg'),
			'exists' => is_file(getenv('ffmpeg')),
			'DATA_STORAGE' => getenv('DATA_STORAGE'),
			'destination' => $meta->getDestination(),
		]);

		$thumb = new Thumb($meta);

		try {
			$thumb->getThumb();    // make it if doesn't exist
		} catch (NotReadableException $e) {
			$content[] = $e;
		} catch (NotWritableException $e) {
			$content[] = $e;
		}
		$content[] = getDebug($thumb);
		$dirDestination = dirname($meta->getDestination());
//		$parent = dirname($dirDestination);
		$content[] = getDebug([
			'exists' => $thumb->exists(),
			'dirname' => $dirDestination,
			'dir exist' => is_dir($dirDestination),
//			'scandir' => scandir($dirDestination),
//			'parent' => $parent,
//			'scandir dir ' => scandir($parent),
//			'scandir O:' => scandir('o:\\'),
		]);

		$content[] = '<p>' . new HTMLTag('a', [
				'href' => $this->request->getURL()
					->setParam('action', 'deleteThumb')
					->setParam('file', $file),
			], 'Delete Thumb') . '</p>';

		$content[] = HTMLTag::img(ShowThumb::href(['file' => $file]), [
			'border' => 1,
		]);

		$thumbPath = $meta->getDestination();
		if ($this->request->getBool('d') || !$thumb->exists()) {
			return $content;
		}

		header('Content-Type: ' . mime_content_type($thumbPath));
		header('Content-Length: ' . filesize($thumbPath));
		$this->request->setCacheable(60 * 60 * 24 * 365);
		readfile($thumbPath);
	}

	public function deleteThumb()
	{
		$file = $this->request->getIntRequired('file');
		$meta = MetaForSQL::findByID($this->db, $file);
		unlink($meta->getDestination());
		$this->request->goBack();
	}

}
