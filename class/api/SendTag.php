<?php

class SendTag extends AppController
{

	/** @var DBInterface */
	protected DBInterface $db;

	public function __construct(DBInterface $db)
	{
		parent::__construct();
		$this->db = $db;
	}

	public function index()
	{
		$id = $this->request->getIntRequired('file');
		$file = MetaForSQL::findByID($this->db, $id);
		$tag = $this->request->getTrimRequired('tag');

		$ttag = TagModel::insert($this->db, [
			'id_file' => $file->id,
			'tag' => $tag,
		]);

		$this->request->json([
			'status' => 'ok',
			'ttag' => $ttag,
		]);
	}

}
