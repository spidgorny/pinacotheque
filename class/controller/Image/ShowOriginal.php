<?php

class ShowOriginal extends AppController
{

	/** @var DBInterface */
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
			return $this->index();
		} catch (Exception $e) {
			return $this->template([
				HTMLTag::div($e, ['class' => 'is-danger']),
			]);
		}
	}

	public function index()
	{
		$file = $this->request->getTrim('file');
		if (!$file) {
			throw new InvalidArgumentException('Provide file parameter');
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

		$content[] = HTMLTag::img(ShowOriginal::href(['file' => $file]));

		if ($this->request->getBool('d') || !filesize($filePath)) {
			return $content;
		}

		header('Content-Type: ' . mime_content_type($filePath));
		header('Content-Length: ' . filesize($filePath));
		$this->request->setCacheable(60*60*24*365);
		readfile($filePath);
	}

}
