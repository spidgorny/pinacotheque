<?php

class Tags extends AppController
{

	/** @var DBInterface */
	protected DBInterface $db;

	public function __construct(DBInterface $db)
	{
		parent::__construct();
		$this->db = $db;
		header('Access-Control-Allow-Origin: http://localhost:3000');
	}

	public function index()
	{
		$id = $this->request->getIntRequired('id');
		$file = MetaForSQL::findOne($this->db, [
			'id' => $id,
		]);
		$method = $this->request->getMethod();
		return $this->$method($file);
	}

	public function GET(MetaForSQL $file)
	{
		$tags = $file->loadTags();
		return new JSONResponse([
			'status' => 'ok',
			'tags' => $tags,
		]);
	}

	public function POST(MetaForSQL $file)
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
