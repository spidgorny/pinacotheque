<?php

class Tags extends AppController
{

	protected DBInterface $db;

	public function __construct(DBInterface $db)
	{
		parent::__construct();
		$this->db = $db;
		header('Access-Control-Allow-Origin: http://localhost:3000');
		header('Access-Control-Allow-Headers: content-type');
	}

	public function index()
	{
		$file = null;
		$id = $this->request->getInt('id');
		if ($id) {
			$file = MetaForSQL::findOne($this->db, [
				'id' => $id,
			]);
		}
		$method = $this->request->getMethod();
		return $this->$method($file);
	}

	public function OPTIONS(): JSONResponse
	{
		return new JSONResponse(['status' => 'ok']);
	}

	public function GET(MetaForSQL $file): JSONResponse
	{
		$tags = $file->loadTags();
		return new JSONResponse([
			'status' => 'ok',
			'tags' => $tags,
		]);
	}

	public function POST(MetaForSQL $file): JSONResponse
	{
		$oldTags = $file->loadTags();
		$newTags = $this->request->getJsonPost();
		$removeTags = array_diff($oldTags, $newTags);
		$insertTags = array_diff($newTags, $oldTags);
		foreach ($insertTags as $tag) {
			TagModel::insert($this->db, [
				'id_file'=>$file->id,
				'tag' => $tag
			]);
		}
		return new JSONResponse([
			'oldTags' => $oldTags,
			'newTags' => $newTags,
			'insertTags' => $insertTags,
			'removeTags' => $removeTags,
		]);
	}

}
