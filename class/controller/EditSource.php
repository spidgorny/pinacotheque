<?php

class EditSource extends AppController
{

	protected $db;

	public function __construct(DBLayerSQLite $db)
	{
		parent::__construct();
		$this->db = $db;
	}

	public function __invoke()
	{
	    $sourceID = $this->request->getIntRequired('source');
	    $source = Source::findByID($this->db, $sourceID);
		$form = new HTMLFormTable([
			'name' => [
				'label' => 'Name',
			],
			'path' => [
				'label' => 'Path',
			],
			'thumbsRoot' => [
				'label' => 'ThumbsRoot',
			],
		]);
		$form->fill((array)$source);
		$content[] = $form->getContent();
		$content[] = getDebug($form);
		return $this->template($content);
	}

}
