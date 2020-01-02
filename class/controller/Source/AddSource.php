<?php

class AddSource extends SourceForm
{

	/** @var DBLayerSQLite */
	protected $db;

	public function __construct(DBLayerSQLite $db)
	{
		parent::__construct();
		$this->db = $db;
	}

	public function index()
	{
	    $form = $this->getForm([]);
		$form->submit('Add', ['class' => 'button is-link']);
		$form->hidden('action', 'add');
		$content[] = $form->getContent();
//		$content[] = getDebug($form);
		return $this->template($content);
	}

	public function add()
	{
		Source::insert($this->db, [
			'name' => $this->request->getTrim('name'),
			'path' => $this->request->getTrim('path'),
			'thumbRoot' => $this->request->getTrim('thumbRoot'),
		]);
		$this->request->goBack();
	}

}
