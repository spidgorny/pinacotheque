<?php

class GetMetaInfo extends AppController
{

	/**
	 * @var DBInterface
	 */
	protected $db;

	public function __construct(DBInterface $db)
	{
		parent::__construct();
		$this->db = $db;
	}

	public function index()
	{
		$content[] = '<div class="is-size-7 m-3" style="overflow-wrap: break-word">';
		$id = $this->request->getIntRequired('file');
		$file = MetaForSQL::findByID($this->db, $id);
		$metaData = $file->getMeta();

		if ($this->request->getHeader('Accept') === 'application/json') {
			$this->outputJson($file, $metaData);
			return;
		}

		$content = [
			HTMLTag::div('Tags: ' . implode(', ', $this->getTags($file))),
		];
		foreach ($metaData as $metaEntry) {
			$content[] = HTMLTag::div($metaEntry->name, ['class' => 'has-text-weight-bold']);
			$content[] = HTMLTag::div($metaEntry->value, ['class' => 'mb-3']);
		}

		$content[] = '</div>';
		return $content;
	}

	public function outputJson(MetaForSQL $file, array $metaData)
	{
		$metaSet = [
			'tags' => $this->getTags($file),
		];
		foreach ($metaData as $metaEntry) {
			$metaSet[$metaEntry->name] = $metaEntry->value;
		}
		$this->request->json($metaSet);
	}

	public function getTags(MetaForSQL $file)
	{
		$tagNames = [];
		/** @var TagModel[] $tags */
		$tags = TagModel::findAll($this->db, [
			'id_file' => $file->id,
		]);
		foreach ($tags as $entry) {
			$tagNames[] = $entry->tag;
		}
		return $tagNames;
	}

}
