<?php

class ScanMeta extends ApiController
{

	/**
	 * @var DBInterface
	 */
	protected DBInterface $db;

	public function __construct(DBInterface $db)
	{
		parent::__construct();
		$this->db = $db;
		header('Access-Control-Allow-Origin: http://localhost:3000');
		header('Content-Type: application/json');
	}

	public function index()
	{
		$fileID = $this->request->int('fileID');
		if (!$fileID) {
			throw new Exception('array file not provided');
		}

		$file = MetaForSQL::findByID($this->db, $fileID);
		if (!$file) {
			throw new Error('File not found');
		}

		$is = new ImageScanner($file, $this->db);
		[$meta, $thumb] = $is();

		$URL = $this->request->getURL();
		$host = $URL->getHost() ?? ('http://' . $_SERVER['HTTP_HOST']);
		return new JSONResponse([
			'status' => 'ok',
			'meta' => $meta,
			'thumb' => $thumb,
			'thumbUrl' => $file->getThumbnail($host . '/ShowThumb?file='),
			'thumbMeta' => $file->getThumb()->getMeta(),
		]);
	}

}
