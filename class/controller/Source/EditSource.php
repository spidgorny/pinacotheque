<?php

class EditSource extends SourceForm
{

	/**
	 * @var DBLayerSQLite
	 */
	protected $db;

	/** @var Source */
	protected $source;

	public function __construct(DBLayerSQLite $db)
	{
		parent::__construct();
		$this->db = $db;
		$sourceID = $this->request->getIntRequired('source');
		$this->source = Source::findByID($this->db, $sourceID);
	}

	public function index()
	{
	    $form = $this->getForm((array)$this->source);
		$form->submit('Save', ['class' => 'button is-link']);
		$form->hidden('action', 'save');
		$content[] = $form->getContent();
//		$content[] = getDebug($form);
		return $this->template($content);
	}

	public function save()
	{
		$this->source->update([
			'name' => $this->request->getTrim('name'),
			'path' => $this->request->getTrim('path'),
			'thumbRoot' => $this->request->getTrim('thumbRoot'),
		]);
		$this->request->goBack();
	}

}
