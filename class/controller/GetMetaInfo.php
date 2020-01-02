<?php

class GetMetaInfo extends AppController
{

	protected $db;

	public function __construct(DBLayerSQLite $db)
	{
		parent::__construct();
		$this->db = $db;
	}

	public function index()
	{
		$content[] = '<div class="is-size-7 m-3" style="overflow-wrap: break-word">';
		$file = MetaForSQL::findByID($this->db, $this->request->getIntRequired('file'));
		$metaData = $file->getMeta();

		foreach ($metaData as $metaEntry) {
			$content[] = HTMLTag::div($metaEntry->name, ['class' => 'has-text-weight-bold']);
			$content[] = HTMLTag::div($metaEntry->value, ['class' => 'mb-3']);
		}

		$content[] = '</div>';
		return $content;
	}

}
